<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Controller\Adminhtml\Post;

/**
 * Blog post save controller
 */
class Save extends \Magefan\Blog\Controller\Adminhtml\Post
{
	/**
	 * Before model save
	 * @param  \Magefan\Blog\Model\Post $model
	 * @param  \Magento\Framework\App\Request\Http $request
	 * @return void
	 */
	protected function _beforeSave($model, $request)
	{
		if ($links = $request->getParam('links')) {

			foreach (array('post', 'product') as $key) {
				$param = 'related'.$key.'s';
				if (!empty($links[$param])) {
					$ids = array_unique(
						array_map('intval',
							explode('&', $links[$param])
						)
					);
					if (count($ids)) {
						$model->setData('related_'.$key.'_ids', $ids);
					}
				}
			}
		}
	}
	
}
