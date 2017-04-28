define(['uiComponent', 'Magento_Customer/js/customer-data', 'ko'], function(Component, customerData, ko) {

    return Component.extend({
        variables: {},

        initialize: function () {
            //var self = this,
            //koexampleData = customerData.get('koexample');
            //console.log(koexampleData());
            //this.update(koexampleData());

            this._super();
            //var d = new Date();
            //this.sayHello = "Hello this is content populated with KO! " + d.getTime();
            this.variables = customerData.get('recommend');
        }
        //,
        //isLoading: ko.observable(false),
        /**
         * Update mini shopping cart content.
         *
         * @param {Object} updatedCart
         * @returns void
         */
        //update: function (recommendData) {
        //    console.log('update');
        //    _.each(recommendData, function (value, key) {
        //        console.log(value);
        //        console.log(key);
        //        if (!this.variables.hasOwnProperty(key)) {
        //            this.variables[key] = ko.observable();
        //        }
        //        this.variables[key](value);
        //    }, this);
        //},

        /**
         * Get cart param by name.
         * @param {String} name
         * @returns {*}
         */
        //getVariable: function (name) {
        //    console.log('getVariable');
        //    console.log(name);
        //    if (!_.isUndefined(name)) {
        //        if (!this.variables.hasOwnProperty(name)) {
        //            this.variables[name] = ko.observable();
        //        }
        //    }
        //
        //    return this.variables[name]();
        //}

    });
});