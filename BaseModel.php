<?php
/**
 * @package Basic App Core
 * @license MIT License
 * @link    http://basic-app.com
 */
namespace BasicApp\Core;

use Exception;

abstract class BaseModel extends \CodeIgniter\Model
{

    use FactoryTrait;

    protected $beforeInsert = ['beforeInsert'];

    protected $afterInsert = ['afterInsert'];

    protected $beforeUpdate = ['beforeUpdate'];

    protected $afterUpdate = ['afterUpdate'];

    protected $afterFind = ['afterFind']; 

    protected $beforeDelete = ['beforeDelete'];

    protected $afterDelete = ['afterDelete'];

	protected static $fieldLabels = [];

	public function getPrimaryKey()
	{
		return $this->primaryKey;
	}

	public function getReturnType()
	{
		return $this->returnType;
	}

	public static function getFieldLabels()
	{
		return static::$fieldLabels;
	}

    public static function fieldLabel($field, $default = null)
    {
        $labels = static::getFieldLabels();

        if (array_key_exists($field, $labels))
        {
            return $labels[$field];
        }

        if ($default === null)
        {
        	return $field;
        }

        return $default;
    }

	public function errors(bool $forceDB = false)
	{
		$errors = parent::errors($forceDB);

		if ($errors)
		{
			$labels = $this->getFieldLabels();

			foreach($errors as $key => $value)
			{
				$errors[$key] = strtr($errors[$key], $labels);
			}	
		}
		
		return $errors;
	}

	public static function createEntity(array $params = [])
	{
		if ($this->returnType === 'array')
		{
			return $params;
		}

		$returnType = $this->returnType;

		$model = new $returnType;

		foreach($params as $key => $value)
		{
			$model->$key = $value;
		}

		return $model;
	}

	public static function getEntity(array $where, bool $create = false, array $params = [])
	{
		$class = static::class;

		$model = new $class;

		$row = $model->where($where)->first();

		if ($row)
		{
			return $row;
		}

		if (!$create)
		{
			return null;
		}

		foreach ($where as $key => $value)
		{
			$params[$key] = $value;
		}

		$model->protect(false);

		$b = $model->insert($params);

		$model->protect(true);

		if (!$b)
		{
			// nothing to do
		}

		$row = $model->where($where)->first();

		if (!$row)
		{
			throw new Exception('Entity not found.');
		}

		return $row;
	}

    protected function beforeInsert(array $params)
    {
        return $params;
    }

    protected function afterInsert(array $params)
    {
    }

    protected function beforeUpdate(array $params)
    {
        return $params;
    }

    protected function afterUpdate(array $params)
    {
    }

    protected function afterFind(array $params)
    {
        return $params;
    }

    protected function beforeDelete(array $params)
    {
    }

    protected function afterDelete(array $params)
    {
    }

}