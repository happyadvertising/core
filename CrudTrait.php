<?php
/**
 * @package Basic App Core
 * @license MIT License
 * @link    http://basic-app.com
 */
namespace BasicApp;

use Config\Services;
use Exception;
use PageNotFoundException;

trait CrudTrait
{

	protected function _getProperty($name, $default = null)
	{
		if (property_exists($this, $name))
		{
			return $this->$name;
		}

		if ($default !== null)
		{
			return $default;
		}

		throw new Exception('Property "' . $name . '" not defined.');
	}

	protected function getModelClass()
	{
		return $this->_getProperty('modelClass');
	}

	protected function getParentField()
	{
		return $this->_getProperty('parentField', false);
	}	

	protected function getIndexView()
	{
		return $this->_getProperty('indexView', 'index');
	}

	protected function getCreateView()
	{
		return $this->_getProperty('createView', 'create');
	}

	protected function getUpdateView()
	{
		return $this->_getProperty('updateView', 'update');
	}

	protected function getPerPage()
	{
		return $this->_getProperty('perPage', false);
	}

	protected function getOrderBy()
	{
		return $this->_getProperty('orderBy', false);
	}

	protected function getReturnUrl()
	{
		return $this->_getProperty('returnUrl');
	}

	protected function redirectBack($returnUrl)
	{
		$url = $this->request->getGet('returnUrl');

		if (!$url)
		{
			$url = $returnUrl;
		}

		helper(['url']);

		$returnUrl = site_url($url);

    	return Services::response()->redirect($returnUrl);
	}

	protected function fill($model, $values)
	{
		if (is_array($model))
		{
			foreach($values as $key => $value)
			{
				$model[$key] = $value;
			}
		}
		else
		{
			$model->fill($values);
		}

		return $model;
	}

	protected function save($model)
	{
		$query = $this->createQuery();

		$query->save($model);

		$errors = $query->errors();

		if ($errors && method_exists($query, 'fieldLabels'))
		{
			foreach($errors as $key => $value)
			{
				$errors[$key] = strtr($errors[$key], $query->fieldLabels());
			}
		}
	
		return $errors;
	}

	public function update($id = false)
	{
		$errors = [];

		$model = $this->find($id);

		$post = $this->request->getPost();

		if ($post)
		{
			$this->fill($model, $post);

			$errors = $this->save($model);

			if (!$errors)
			{
				return $this->redirectBack($this->getReturnUrl());
			}
		}

		$parentId = null;

		$parentField = $this->getParentField();

		if ($parentField)
		{
			$parentId = $model->{$parentField};
		}

		return $this->render($this->getUpdateView(), [
			'errors' => $errors,
			'model' => $model,
			'parentId' => $parentId
		]);
	}


	public static function createEntity()
	{
		if ($this->returnType == 'array')
		{
			$return = [];
		}
		else
		{
			$modelClass = $this->returnType;

			$return = new $modelClass;
		}

		return $return;
	}


	protected function createEntity($parentId = false)
	{
		$modelClass = $this->getModelClass();

		$model = new $modelClass;

		$parentField = $this->getParentField();

		$returnType = $model->getReturnType();

		if ($returnType === 'array')
		{
			$return = [];
		}
		else
		{
			$return = new $returnType;
		}

		if ($parentField && $parentId)
		{
			if (is_array($return))
			{
				$return[$parentField] = $parentId;
			}
			else
			{
				$return->{$this->parentField} = $parentId;
			}
		}

		return $return;
	}

	public function create()
	{
		$errors = [];

		$parentId = $this->request->getGet('parentId');
		
		$model = $this->createEntity($parentId);
		
		$post = $this->request->getPost();

		if ($post)
		{
			$this->fill($model, $post);

			$errors = $this->save($model);

			if (!$errors)
			{
				return $this->redirectBack($this->returnUrl);
			}
		}

		return $this->render($this->getCreateView(), [
			'model' => $model,
			'errors' => $errors,
			'parentId' => $parentId
		]);
	}

	public function delete($id = false)
	{
		$query = $this->createQuery();

		$model = $this->find($id);

		if ($this->request->getPost())
		{
			helper(['get_protected_value']);

			$primaryKey = get_protected_value($query, 'primaryKey');

			if (!$query->delete($model->{$primaryKey}))
			{
				throw new Exception('Record not deleted.');
			}
		}

		return $this->redirectBack($this->returnUrl);
	}

	protected function createQuery()
	{
		$modelClass = $this->getModelClass();

		$query = new $modelClass;

		return $query;
	}

	protected function find($id)
	{
		if (!$id)
		{
			$id = $this->request->getGet('id');
		}

		$query = $this->createQuery();

		$model = $query->find($id);

		if (!$model)
		{
			throw new PageNotFoundException;
		}

		return $model;
	}

	protected function beforeFind($query)
	{
	}

	public function index()
	{
		$query = $this->createQuery();

		$parentId = $this->request->getGet('parentId');

		if ($parentId)
		{
			$query->where($this->getParentField(), $parentId);
		}

		if ($this->getOrderBy())
		{
			$query->orderBy($this->getOrderBy());
		}

		$this->beforeFind($query);

		if ($this->perPage)
		{
			$elements = $query->paginate($this->perPage);
		}
		else
		{
			$elements = $query->findAll();
		} 

		return $this->render($this->getIndexView(), [
			'elements' => $elements,
			'pager' => $query->pager,
			'parentId' => $parentId
		]);
	}

}