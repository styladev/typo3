
plugin.tx_ecstyla_contenthub {
    view {
        templateRootPaths.0 = EXT:ec_styla/Resources/Private/Templates/
        templateRootPaths.1 = {$plugin.tx_ecstyla_contenthub.view.templateRootPath}
        partialRootPaths.0 = EXT:ec_styla/Resources/Private/Partials/
        partialRootPaths.1 = {$plugin.tx_ecstyla_contenthub.view.partialRootPath}
        layoutRootPaths.0 = EXT:ec_styla/Resources/Private/Layouts/
        layoutRootPaths.1 = {$plugin.tx_ecstyla_contenthub.view.layoutRootPath}
    }
    persistence {
        storagePid = {$plugin.tx_ecstyla_contenthub.persistence.storagePid}
        #recursive = 1
    }
    features {
        #skipDefaultArguments = 1
        # if set to 1, the enable fields are ignored in BE context
        ignoreAllEnableFieldsInBe = 0
        # Should be on by default, but can be disabled if all action in the plugin are uncached
        requireCHashArgumentForActionArguments = 1
    }
    mvc {
        #callDefaultActionIfActionCantBeResolved = 1
    }
}