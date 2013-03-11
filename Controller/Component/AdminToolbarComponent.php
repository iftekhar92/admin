<?php
/**
 * @copyright	Copyright 2006-2013, Miles Johnson - http://milesj.me
 * @license		http://opensource.org/licenses/mit-license.php - Licensed under the MIT License
 * @link		http://milesj.me/code/cakephp/admin
 */

class AdminToolbarComponent extends Component {

	/**
	 * Components.
	 *
	 * @var array
	 */
	public $components = array('Auth');

	/**
	 * Log a users action.
	 *
	 * @param int $action
	 * @param Model $model
	 * @param int $id
	 * @param string $comment
	 * @param string $item
	 * @return bool
	 * @throws InvalidArgumentException
	 */
	public function logAction($action, Model $model = null, $id = null, $comment = null, $item = null) {
		if (!Configure::read('Admin.logActions')) {
			return false;
		}

		$log = ClassRegistry::init('Admin.ActionLog');
		$query = array(
			'user_id' => $this->Auth->user('id'),
			'action' => $action
		);

		// Validate action
		if (!$log->enum('action', $action)) {
			throw new InvalidArgumentException(__('Invalid log action type'));
		}

		if ($model) {
			// Get model name
			if (isset($model->qualifiedName)) {
				$query['model'] = $model->qualifiedName;
			} else {
				$plugin = $model->plugin ?: Configure::read('Admin.coreName');

				$query['model'] = $plugin . '.' . $model->name;
			}

			// Get comment from request
			if (!$comment && isset($this->request->data[$model->alias]['log_comment'])) {
				$comment = $this->request->data[$model->alias]['log_comment'];
			}

			// Get display field from data
			if (!$item && isset($model->data[$model->alias][$model->displayField]) && $model->primaryKey !== $model->displayField) {
				$item = $model->data[$model->alias][$model->displayField];
			}
		}

		$query['foreign_key'] = $id;
		$query['comment'] = $comment;
		$query['item'] = $item;

		return $log->logEvent($query);
	}

}