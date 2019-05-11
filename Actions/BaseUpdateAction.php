<?php
/**
 * @package Basic App Actions
 * @license MIT License
 * @link    http://basic-app.com
 */
namespace BasicApp\Core\Actions;

abstract class BaseUpdateAction extends \BasicApp\Core\Action
{

    public $view;

    public function run(array $options = [])
    {
        $errors = [];

        $row = $this->findEntity();

        $post = $this->request->getPost();

        if ($post)
        {
            $row = $this->fillEntity($row, $post);

            if ($this->saveEntity($row, $errors))
            {
                return $this->redirectBack($this->returnUrl);
            }
        }

        return $this->render($this->view, [
            'errors' => $errors,
            'model' => $row,
            'parentId' => $this->entityParentKey($row)
        ]);
    }

}