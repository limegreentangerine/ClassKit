<?php

namespace ClassKit\File;

use Core;
use FileSet;
use Exception;
use RuntimeException;
use Concrete\Core\Url\Url;
use IPLib\Factory as IPFactory;
use Concrete\Core\Tree\Node\Node;
use Concrete\Core\File\Filesystem;
use IPLib\Range\Type as IPRangeType;
use Concrete\Core\File\Import\FileImporter;
use Concrete\Core\Tree\Node\Type\FileFolder;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Entity\File\File as FileEntity;
use Concrete\Core\File\Service\File as FileHelper;
use Concrete\Core\Entity\File\Version as FileVersion;
use Concrete\Core\Entity\File\Version as FileVersionEntity;
use Concrete\Core\File\StorageLocation\StorageLocationFactory;

/**
 * Trait ImportFileTrait.
 */
trait ImportFileTrait
{
    /**
     * Main Import Image Function
     *
     * @var string $imageUrl
     * @var string $folderName
     * @var bool   $returnFile Choose to return FileEntity or FileVersion
     *
     * @throws UserMessageException
     *
     * @return FileEntity|FileVersion
     */
    protected function importImage(string $imageUrl, ?string $folderName, ?string $setName, bool $returnFile = false)
    {
        $folder = (isset($folderName)) ? $this->getOrSetFileFolder($folderName) : false;
        $fileset = (isset($setName)) ? FileSet::createAndGetSet($setName, FileSet::TYPE_PUBLIC, 1) : false;
        $image = $this->importImageFromUrl($imageUrl);

        if ($image instanceof FileVersionEntity) {
            $imageFile = $image->getFile();

            // add to fileset if applicable
            if ($fileset) {
                $fileset->addFileToSet($imageFile);
            }

            // add to file folder if applicable
            if ($folder) {
                $imageFileNode = $imageFile->getFileNodeObject();
                if ($imageFileNode instanceof Node) {
                    $imageFileNode->move($folder);
                }
            }
        } else {
            throw new UserMessageException(t('Error importing image (%s)', $imageUrl));
        }

        return ($returnFile) ? $image->getFile() : $image;
    }

    /**
     * Get or Set File Folder
     *
     * @var string $folderName
     *
     * @return ?FileFolder
     */
    protected function getOrSetFileFolder(string $folderName)
    {
        if ($folderName !== null && $folderName !== '') {
            $folder = new FileFolder();
            $folder = $folder->getNodeByName($folderName);

            if ($folder == null) {
                $storageLocation = Core::make(StorageLocationFactory::class)->fetchDefault();
                $fslId = $storageLocation->getID();

                $filesystem = new Filesystem();
                $folder = $filesystem->addFolder($filesystem->getRootFolder(), $folderName, $fslId);
            }

            return $folder;
        }

        return null;
    }

    /**
     * Import Image into File Manager from URL
     * - Returns file version entity (https://documentation.concretecms.org/api/9.1.2/Concrete/Core/Entity/File/Version.html)
     *
     * @param string $url
     *
     * @throws UserMessageException|Exception
     *
     * @return ?FileVersion
     */
    protected function importImageFromUrl(string $url): ?FileVersion
    {
        $this->checkRemoteURlsToImport($url);
        $fi = Core::make(FileImporter::class);

        $fh = new FileHelper();
        $fileContent = $fh->getContents($url);
        $fileName = $this->getFileNameFromUrl($url);
        if ($fileContent !== false) {
            $fh->append($fh->getTemporaryDirectory() . '/' . $fileName, $fileContent);

            $fileVersion = $fi->import($fh->getTemporaryDirectory() . '/' . $fileName, $fileName);
            $fh->clear($fh->getTemporaryDirectory() . '/' . $fileName);

            if (!$fileVersion instanceof FileVersionEntity) {
                throw new UserMessageException($url . ': ' . $fi->getErrorMessage($fileVersion));
            }

            return $fileVersion;
        }

        return null;
    }

    /**
     * Check that a list of strings are valid "incoming" file names.
     *
     * @param string $u
     *
     * @throws UserMessageException in case one or more of the specified URLs are not valid
     */
    protected function checkRemoteURlsToImport(string $u): void
    {
        try {
            $url = Url::createFromUrl($u);
        } catch (RuntimeException $x) {
            throw new UserMessageException(t('The URL "%s" is not valid: %s', $u, $x->getMessage()));
        }
        $scheme = strtolower((string) $url->getScheme());
        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new UserMessageException(t('The URL "%s" is not valid.', $u));
        }
        $host = trim((string) $url->getHost());
        if (in_array(strtolower($host), ['', '0', 'localhost'], true)) {
            throw new UserMessageException(t('The URL "%s" is not valid.', $u));
        }
        $ip = IPFactory::parseAddressString($host);
        if ($ip === null) {
            $dnsList = @dns_get_record($host, DNS_A | DNS_AAAA);
            while ($ip === null && $dnsList !== false && count($dnsList) > 0) {
                $dns = array_shift($dnsList);
                $ip = IPFactory::parseAddressString($dns['ip']);
            }
        }
        if ($ip !== null && $ip->getRangeType() !== IPRangeType::T_PUBLIC) {
            throw new UserMessageException(t('The URL "%s" is not valid.', $u));
        }

        // inspect images size to see if it exceeds dimension limits if set
        $config = Core::make('config');
        $imgMaxWidth = (int) $config->get('concrete.file_manager.restrict_max_width');
        $imgMaxHeight = (int) $config->get('concrete.file_manager.restrict_max_height');
        $imageInfo = getimagesize($u);

        if ($imageInfo) {
            if ($imgMaxWidth > 0 && $imgMaxHeight > 0) {
                $imgWidth = (int) $imageInfo[0];
                $imgHeight = (int) $imageInfo[1];

                if ($imgWidth > $imgMaxWidth) {
                    throw new UserMessageException(t('Image width (%s) is larger than the set max width (%).', $imgWidth, $imgMaxWidth));
                }

                if ($imgHeight > $imgMaxHeight) {
                    throw new UserMessageException(t('Image height (%s) is larger than the set max height (%).', $imgHeight, $imgMaxHeight));
                }
            }
        } else {
            throw new UserMessageException(t('Unable to get image size for url (%s)', $u));
        }
    }

    /**
     * Get Filename from URL
     *
     * @var string $url
     *
     * @throws UserMessageException in case it can't determine the filename
     *
     * @return string
     */
    protected function getFileNameFromUrl(string $url): string
    {
        if (preg_match('/^[^#\?]+[\\/]([-\w%]+\.[-\w%]+)($|\?|#)/', $url, $matches)) {
            // got a filename (with extension)... use it
            $filename = $matches[1];
        } else {
            $headers = get_headers($url, true);
            $contentType = $headers['Content-Type'];
            if ($contentType) {
                list($mimeType) = explode(';', $contentType, 2);
                $mimeType = trim($mimeType);
                $extension = Core::make('helper/mime')->mimeToExtension($mimeType);
                if ($extension === false) {
                    throw new UserMessageException(t('Unknown mime-type: %s', h($mimeType)));
                }
                $filename = date('Y-m-d_H-i_') . mt_rand(100, 999) . '.' . $extension;
            } else {
                throw new UserMessageException(t(/*i18n: %s is an URL*/'Could not determine the name of the file at %s', $url));
            }
        }

        return $filename;
    }
}
