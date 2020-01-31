# ValitorOmni plugin for Magento 2.3

== Change log ==

** Version 1.9.0

    * Improvements:
            - Added a fix in relation to a bug in Magento core source code
            - Completed the rebranding changes
            - Revamped orderlines for capture and refund calls
            - Added support for bundle product and multiple tax rules
    * Bug fixtures:
            - Failed order when coupon code applied only to shipping
            - Duplicated confirmation email sent when e-payments
            - Rounding mismatch issue in compensation amounts

** Version 1.8.0

    * Improvements: 
            - Revamp orderlines on various coupon scenarios
            - New enhancements related to various types of discounts and tax scenarios
            - Compensation amount with shipping
            - Added support for:
                - catalog discounts in relation to the latest updates on orderlines 
                - applied discount on virtual products
    * Bug fixes:
            - Partial captures failing on certain cases when Klarna used as payment method
            - Exception thrown on certain cases when refunds are made 


** Version 1.7.0

    * Improvement:
            - Added support for configurable products
            

** Version 1.6.0

    * Improvements:
            - List of supported languages is dynamically fetched; only supported by the payment gateway are available
            - Terminal dropdown list with default option
            - Added support discounts applied to shipping
            - Handle correctly virtual and downloadable products at checkout completion
    * Bug fixes:
            - Payment method not always shown correctly, according to the store configuration
            - Multiple issues on shipping orderline
            - Terminal enabled based on Default instead of Store configuration level
            - Capture and refund failing on certain cases
            - Amounts having more than two digits not handled correctly
            - Error message related to the back button shown when successful payment

** Version 1.5.0

    * Improvements:
        - Added support for coupons
        - Browser back button improvements
        - Separate order line for cart rules sent the payment gateway
        - Improvements on handling discounts on price including tax
        - Changed private methods to protected to allow easier rewrites(credits to Martin René Sørensen, through pull request)
    * Bug fixtures:
        - Unit price not fetched correctly on price including taxes
        - Order status history comment added when consumer gets redirected to the payment gateway


** Version 1.4.0

    * Improvement:
        - Added more details in the history comment for failed orders
    * Note:
        - Only discounts in percentage, two digits, are supported for payments made with Klarna


** Version 1.3.1

    * Bug Fixes
        - 302 errors due to the missing of CsrfValidator
        - Klarna error message not shown to the consumer
            

** Version 1.3.0

    * Improvement:
        - New database table according to the branding changes                  
        - Several refactored files                     
        - Database update for cleanup job after the rebraning changes
        - Added a second batch of branding changes (renamed layout files and references)
    * Bug Fixes
        - Error not showing on browser back buton usage.
        - Discounts not handled properly due to unitPrice and discount percentage
        - PHP 7.2 limitation has been removed


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
