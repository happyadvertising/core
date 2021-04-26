<?php
/**
 * @author Basic App Dev Team <dev@basic-app.com>
 * @license MIT
 * @link http://basic-app.com
 */

namespace BasicApp\Core;

use CodeIgniter\Database\Exceptions\DataException;
use Exception;

use denis303\traits\FactoryTrait;
use denis303\traits\DefaultPropertyTrait;
use BasicApp\Traits\BehaviorsTrait;
use BasicApp\Traits\LanguageTrait;
use BasicApp\Traits\FieldLabelsTrait;
use denis303\codeigniter4\ModelHelperTrait;

abstract class BaseModel extends \CodeIgniter\Model
{

    use ModelHelperTrait;

    use FactoryTrait;

    use DefaultPropertyTrait;

    use BehaviorsTrait;

    use LanguageTrait;

    use FieldLabelsTrait;

    protected $afterFind = ['afterFind'];

    protected $beforeInsert = ['beforeInsert'];

    protected $afterInsert = ['afterInsert'];

    protected $beforeUpdate = ['beforeUpdate'];

    protected $afterUpdate = ['afterUpdate'];

    protected $beforeDelete = ['beforeDelete'];

    protected $afterDelete = ['afterDelete'];

    protected $beforeSave = ['beforeSave'];

    protected $afterSave = ['afterSave'];

    protected $beforeValidate = ['beforeValidate'];

    protected $afterValidate = ['afterValidate'];

    protected $fieldLabels = [];

    protected $languageCategory = false;

    public function getValidation()
    {
        return $this->validation;
    }

    /**
     * When I save the model without changes i get an error: "You must use the "set" method to update an entry."
     * To fix this I change the $onlyChanged default value from "true" to "false".
     *
     * ToDo: Remove this later...
     */
    public static function classToArray($data, $primaryKey = null, string $dateFormat = 'datetime', bool $onlyChanged = false): array
    {
        return parent::classToArray($data, $primaryKey, $dateFormat, $onlyChanged);
    }

    /**
     * I do not know why the values ​​are not checked, if the user did not pass them to the server.
     * Just turn it off here.
     *
     * ToDo: Remove this later...
     */
//    protected function cleanValidationRules(array $rules, array $data = null): array
//    {
//        return $rules;
//    }

    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    public function getReturnType()
    {
        return $this->returnType;
    }

    public function errors(bool $forceDB = false)
    {
        $this->db->initialize(); // Bug in CI? parent::errors() function throw an error if database is not initialized.

        $errors = parent::errors($forceDB);

        if (!$errors) {
            $errors = null;
        }

        if ($errors) {
            $labels = $this->getFieldLabels();

            foreach ($errors as $key => $value) {
                $errors[$key] = strtr($errors[$key], $labels);
            }
        }

        return $errors;
    }

    public function afterFind(array $params): array
    {
        foreach (array_keys($this->behaviors()) as $behavior) {
            $params = $this->as($behavior)->afterFind($params);
        }

        return $params;
    }

    public function beforeInsert(array $params): array
    {
        foreach (array_keys($this->behaviors()) as $behavior) {
            $params = $this->as($behavior)->beforeInsert($params);
        }

        return $params;
    }

    public function afterInsert(array $params)
    {
        foreach (array_keys($this->behaviors()) as $behavior) {
            $this->as($behavior)->afterInsert($params);
        }
    }

    public function beforeUpdate(array $params): array
    {
        foreach (array_keys($this->behaviors()) as $behavior) {
            $params = $this->as($behavior)->beforeUpdate($params);
        }

        return $params;
    }

    public function afterUpdate(array $params)
    {
        foreach (array_keys($this->behaviors()) as $behavior) {
            $this->as($behavior)->afterUpdate($params);
        }
    }

    public function beforeDelete(array $params)
    {
        foreach (array_keys($this->behaviors()) as $behavior) {
            $this->as($behavior)->beforeDelete($params);
        }
    }

    public function afterDelete(array $params)
    {
        foreach (array_keys($this->behaviors()) as $behavior) {
            $this->as($behavior)->afterDelete($params);
        }
    }

    protected function beforeSave(array $params): array
    {
        foreach (array_keys($this->behaviors()) as $behavior) {
            $params = $this->as($behavior)->beforeSave($params);
        }

        return $params;
    }

    protected function afterSave(array $params): array
    {
        foreach (array_keys($this->behaviors()) as $behavior) {
            $params = $this->as($behavior)->afterSave($params);
        }

        return $params;
    }

    protected function beforeValidate(array $params): array
    {
        foreach (array_keys($this->behaviors()) as $behavior) {
            $params = $this->as($behavior)->beforeValidate($params);
        }

        return $params;
    }

    protected function afterValidate(array $params): array
    {
        foreach (array_keys($this->behaviors()) as $behavior) {
            $params = $this->as($behavior)->afterValidate($params);
        }

        return $params;
    }

    public function validate($data): bool
    {
        // save validation rules

        $validationRules = $this->validationRules;

        // prepare validation rules and data

        $params = $this->trigger('beforeValidate', ['validationRules' => $validationRules, 'data' => $data]);

        $this->validationRules = $params['validationRules'];

        $data = $params['data'];

        // Query Builder works with objects as well as arrays, but not with CodeIgniter\Entity objects...
        //
        // ToDo: Remove this later...

        if (is_object($data) && method_exists($data, 'toArray')) {
            $values = $data->toArray();
        } else {
            $values = $data;
        }

        // 

        $result = parent::validate($values);

        // call validate behavior

        $params = $this->trigger('afterValidate', ['data' => $data, 'result' => $result]);

        // restore validation rules

        $this->validationRules = $validationRules;

        // return result

        return $params['result'];
    }

    public function save($data): bool
    {
        $params = $this->trigger('beforeSave', ['data' => $data]);

        $data = $params['data'];

        $result = false;
        try {
            $result = parent::save($data);
        } catch (DataException $e) {
            print($e->getCode() . ' - ' . $e->getMessage());
        }

        if ($result && $this->insertID) {
            static::setEntityField($data, $this->primaryKey, $this->insertID);
        }

        $params = $this->trigger('afterSave', ['data' => $data, 'result' => $result]);

        return $params['result'];
    }

}