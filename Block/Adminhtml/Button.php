<?php

namespace Magefan\Blog\Block\Adminhtml;

class Button extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected function getJs() {
        $url = $this->getUrl('blog/block/grid');
        $postIds = (string)$this->_scopeConfig->getValue(
            'mfblog/sidebar/featured_posts/posts_ids',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return "require([
                 'jquery',
                 'Magento_Ui/js/modal/modal'
                  ], function($, alert) {
              $(document).ready(function(){
               window.reload = true;
               console.log($('#mfblog_sidebar_featured_posts_posts_ids').value);
               window.postState = '". $postIds . "'.split(',');
              if($('#tyty').children().length === 0){
                var options = {
                      type: 'popup',
                      responsive: true,
                      innerScroll: true,
                       buttons: [{
                           text: 'Continue',
                           class: 'action-default primary add',
                           click: function () {
                               var tr = $('input:checked').parentsUntil('tbody');
                               if (tr.length) {
                                  var arr = {};
                                  tr.children('td').each(function() {
                                    var className = this.className.substring(this.className.indexOf('col-')).replace(/\s/g,'');
                                    arr[className] = this.textContent.replace(/\s/g,'');
                                  });
                                  for ( var key in arr ) {
                                     $('#edit_'+key).val(arr[key]);
                                  }
                               }
                               this.closeModal();
                           }
                      }]
                      };

                 var curl = '" . $url . "';
                 $.ajax({
                   url: curl,
                   type: 'GET',
                   success: function(data) {
                      var result = $(data).find('#order_edit_base_fieldset_grid');
                      $('#tyty').html(result.html()).modal(options).modal('openModal');
                   },
                   error: function(xhr, status, errorThrown) {
                      console.log('Error happens. Try again.');
                   },
                    complete: function (xhr, status) {
                      //$('#showresults').slideDown('slow')
                     }
                 });
              }
              else {
                 $('#tyty').modal('openModal');
              }
              });
         }
        );
";
    }

   protected function _prepareForm()
   {
       $form = $this->_formFactory->create([
           'data' => [
               'id' => 'order_edit_form',
               'action' => 'action',
               'method' => 'post',
               'enctype' => 'multipart/form-data',
           ]
       ]);
       $form->setHtmlIdPrefix('edit_');


       $fieldset = $form->addFieldset(
           'base_fieldset',
           ['legend' => __('General Information'), 'class' => 'fieldset-wide']
       );

       $fieldset->addField('registered', 'button', [
           'value' => ('Change Customer'),
           'class' => 'action-basic',
           'onclick' => $this->getJs(),
       ]);
       $form->setUseContainer(true);
       $this->setForm($form);

       return parent::_prepareForm();
   }
}