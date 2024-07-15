<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\Blog\Block\Adminhtml\System\Config\Form;

use Magefan\Community\Api\SecureHtmlRendererInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Featured extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var SecureHtmlRendererInterface
     */
    private $mfSecureRenderer;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param SecureHtmlRendererInterface $mfSecureRenderer
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        SecureHtmlRendererInterface $mfSecureRenderer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->mfSecureRenderer = $mfSecureRenderer;
    }

    /**
     * @return string
     */
    public function getJs() : string
    {
        $url = $this->getUrl('blog/block_featured/grid');
        return "require([
                 'jquery',
                 'Magento_Ui/js/modal/modal'
                  ], function($, alert) {
                        $(document).ready(function(){
                            /*document.getElementById('mfblog_sidebar_featured_posts_posts_ids').setAttribute('readonly', true);*/
                            
                            var ensureGridIsSet = function (timeout) {
                                var start = Date.now();
                                return new Promise(waitForFoo);
                                
                                function waitForFoo(resolve, reject) {
                                    if (window['post_idsJsObject'])
                                        resolve(window['post_idsJsObject']);
                                    else if (timeout && (Date.now() - start) >= timeout)
                                        reject(new Error('timeout'));
                                    else
                                        setTimeout(waitForFoo.bind(this, resolve, reject), 30);
                                }
                            };
              
                            $('#mfblog_sidebar_featured_posts_posts_ids').dblclick(function() { 
                                window.reload = true;
                                postStateStr = $('#mfblog_sidebar_featured_posts_posts_ids').val();
               
                                if (!postStateStr) {
                                    window.postState = [];
                                }
                                else {
                                    window.postState = postStateStr.split(',');
                                 }
               
              
                                if($('#post_ids_grid').children().length === 0){
                                    var options = {
                                        type: 'popup',
                                        responsive: true,
                                        innerScroll: true,
                                        buttons: [{
                                            text: 'Save',
                                            class: 'action-default primary add',
                                            click: function () {
                                                        var tr = $('input:checked').parentsUntil('tbody');
                                                        if (window.postState.length) {
                                                            $('#mfblog_sidebar_featured_posts_posts_ids').val(window.postState.join(','));
                                                        }
                                                        else {
                                                            $('#mfblog_sidebar_featured_posts_posts_ids').val('');
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
                                            var result = $(data).find('#post_ids_base_fieldset_grid');
                                            $('#post_ids_grid').html(result.html()).modal(options).modal('openModal');
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
                                $('#post_ids .admin__data-grid-wrap.admin__data-grid-wrap-static > table > tbody > tr').each(function () {
                                    var postId = $(this).children('td:nth-child(2)').text().replace(/\s/g,'');
                                    var isChoosed = $('#mfblog_sidebar_featured_posts_posts_ids').val().includes(postId);
                                    
                                    if (isChoosed === true) {
                                        $(this).children('td:first').children('label:first').children('input:first').prop('checked', true);
                                    }
                                    else {
                                        $(this).children('td:first').children('label:first').children('input:first').prop('checked', false);
                                    }
                                });
                                $('#post_ids_grid').modal('openModal');
                              }
    
                            ensureGridIsSet(10000).then(function(){
                                var grid = window['post_idsJsObject'];
                                
                                if (window.postState.length) {
                                    grid.reloadParams = {
                                        'selected_posts[]': window.postState
                                    }; 
                                }
                                else {
                                    grid.reloadParams = {
                                        'selected_posts[]': ['-1']
                                    }; 
                                }
                            });
                           });
              })});";
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element) : string
    {
        $columns = ($this->getRequest()->getParam('website')) || ($this->getRequest()->getParam('store')) ? 5 : 4;
        $js = $this->mfSecureRenderer->renderTag('script', [], $this->getJs(), false);
        return $this->_decorateRowHtml($element, "<td colspan='{$columns}'>" . $this->toHtml() . '<div id="post_ids_grid"></div>' . $js);
    }
}
