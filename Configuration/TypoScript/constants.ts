
plugin.tx_ecstyla_contenthub {
    view {
        # cat=plugin.tx_ecstyla_contenthub/file; type=string; label=Path to template root (FE)
        templateRootPath = EXT:ec_styla/Resources/Private/Templates/
        # cat=plugin.tx_ecstyla_contenthub/file; type=string; label=Path to template partials (FE)
        partialRootPath = EXT:ec_styla/Resources/Private/Partials/
        # cat=plugin.tx_ecstyla_contenthub/file; type=string; label=Path to template layouts (FE)
        layoutRootPath = EXT:ec_styla/Resources/Private/Layouts/
    }
    persistence {
        # cat=plugin.tx_ecstyla_contenthub//a; type=string; label=Default storage PID
        storagePid =
    }
    settings {
    # cat=plugin.tx_ecstyla_contenthub/settings/meta; type=string; label=Meta Tags that should be disabled for styla
        disabled_meta_tags = 
    }
}