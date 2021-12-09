11/04/21 1.1.0.7
update to http context to improve cache
fix switch on api logging
remove stray comment

10/20/21 1.1.0.6
create uicomponent/jsLayout for PDP
move fast button into uicomponent on PDPs
css update for dark theme
resize fast button on pdp

10/19/21 1.1.0.5
fix swagger
reduce number of times /check is called on cart updates

10/18/21 1.1.0.4
correct online v offline credit memo
hide fast button on PDP when product is not saleable (oos)
move PDP checkout js outside of phtml files
prevent ordering product wth 0 qty
allow virtual products in cart

10/14/21
restrict downloadable, virtual and bundle products in minicart/cart

10/05/21
use proper knockout component for minicart fast checkout button
m2 v 2.3 has a core bug that prevents promotion section from showing in minicart so moved fast button from promotion area
to extra_info area
https://github.com/magento/magento2/pull/25375/commits

09/30
add store scope to token

09/28
add auto invoicing feature for Auth+Capture mode to generate invoice when order is shipped

09/23

bugfix: fix user logged out when placing order and fix order not showing in my account

09/21

it is safe to remove the PRODUCT attribute fast_order_item_uuid - it still appears as a quote/order/shipment line item attribute

