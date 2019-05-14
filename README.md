# Valitor-Omni plugin for Magento 2.3

== Change log ==

** Version 1.2.0

    * Improvements:
            - Invoice automatically generated when autocapture or ePayment is used
            - Added section for order status when AutoCapture is on
    * Bug fix:
            - Notification flow broken
            - Wrong module version sent to the payment gateway
                -- before installing this version please run:
                    ```sh
                        $ bin/magento module:uninstall SDM_Altapay -r
                    ```
                
** Version 1.1.0

    * Improvements:
        ** Rebranding from Altapay to Valitor
        ** Platform and plugin versioning information sent to the payment gateway
        ** Added support for virtual products
	
    * Bug fix:
        ** Validation Error not been shown at back button from checkout page
        ** Order Status stall in "Pending"
        ** Payment capture often fails


** Version 1.0.0

    * First version, having as a base the initial Magento 2 plugin (https://github.com/AltaPay/magento2)
    * Note: separate plugin because of the no backward compatibility
