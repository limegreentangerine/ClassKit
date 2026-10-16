<?php

/**
 * @var \Concrete\Core\Page\Page $c
 * @var \Concrete\Package\ClassKit\Controller\SinglePage\Dashboard\SvelteComponents $controller
 * @var \Concrete\Core\View\View $view
 * @var \Concrete\Core\Validation\CSRF\Token $token
 * @var \Concrete\Core\Error\ErrorList\ErrorList $error
 * @var array $paths
 * @var string|null $pathValue
 * @var string|null $pkgHandleValue
 */
?>

<fieldset>
    <legend><?php echo t('Paths to Compile'); ?></legend>

    <?php if (!is_array($paths) || empty($paths)) { ?>
        <div class="alert alert-info"><?php echo t('No svelte compilation paths defined'); ?></div>
    <?php } else { ?>
        <div class="table-responsive">
            <table class="ccm-search-results-table">
                <thead>
                    <tr>
                        <th><?php echo t('Path'); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paths as $index => $path) { ?>
                        <tr>
                            <td><?php echo $path; ?></td>
                            <td><button class="btn btn-sm btn-danger float-end">Delete</button></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>

    <form method="post" action="<?php echo $controller->action('add'); ?>">
        <?php echo $token->output('add_path'); ?>
        <div class="input-group mt-3">
            <?php echo $form->text('path', $pathValue, [ 'placeholder' => t('Path') ]); ?>
            <?php echo $form->text('pkgHandle', $pkgHandleValue, [ 'placeholder' => t('Package Handle') ]); ?>
            <?php echo $form->submit('add', t('Add Path'), [ 'class' => 'btn btn-primary' ]); ?>
        </div>

        <div class="help-block"><?php echo t('Path relative to %s or %s/{PACKAGE_HANDLE}', REL_DIR_APPLICATION, REL_DIR_PACKAGES); ?></div>
    </form>
</fieldset>

<?php if (is_array($paths) || !empty($paths)) { ?>
    <fieldset>
        <legend><?php echo t('Setup'); ?></legend>
        <p><?php echo t('Add this code at the bottom of your <code>webpack.mix.js</code>'); ?></p>

        <pre class="border p-3">
            <code>
                code here
            </code>
        </pre>
    </fieldset>
<?php } ?>
