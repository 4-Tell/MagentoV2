define(['uiComponent', 'Magento_Customer/js/customer-data', 'ko'], function(Component, customerData, ko) {

    return Component.extend({
        variables: {},

        initialize: function () {
            this._super();
            this.variables = customerData.get('recommend');
        }
    });
});