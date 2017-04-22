// js file to apply multiselect js

define(
    [
        'jquery',
        'mage/translate',
        'Magento_Ui/js/modal/alert',
        'Magento_Ui/js/modal/confirm',
        'mage/mage',
        'jquery/ui',
        'jquery/editableMultiselect/js/jquery.editable',
        'jquery/editableMultiselect/js/jquery.multiselect'

    ], function($,$t,alert,confirm) {

        $.widget(
            'fourtell.mselectjs', {
                _create: function() {
                    var self = this;

                    /**
                     * intialize multi select
                     */
                    $(self.options.data.myElement)
                        .multiselect({
                            toggleAddButton:false,
                            parse: null,
                            layout: '<section class="block %mselectListClass%">'
                            +'<div class="block-content"><div class="%mselectItemsWrapperClass%">'
                            +'%items%'
                            +'</div></div>'
                            +'<div class="%mselectInputContainerClass%">'
                            +'<input type="text" class="%mselectInputClass%" title="%inputTitle%"/>'
                            +'</div>'
                            +'</section>',
                            item : '<div  class="%mselectListItemClass% %mselectDisabledClass% %iseditable% %isremovable%"><label><input type="checkbox" value="%value%" %checked% %disabled% /><span>%label%</span></label>' +
                            '</div>',
                            mselectInputSubmitCallback:null,
                        }).parent().find('.mselect-list')
                        .on(
                        'click.mselect-edit',
                        '.mselect-edit',
                        function(){
                            $('body').trigger('processStart');
                            self.editOption($(this));
                            $('body').trigger('processStop');
                        }
                    );
                },
            }
        );

        return $.fourtell.mselectjs;
    }
);