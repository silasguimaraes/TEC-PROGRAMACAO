Passo 7: O Pull Request da Arquitetura
Se o teste passou e tudo está verde, vocês acabaram de implementar o Padrão MVP com maestria!
Objetivo: Revisão de código baseada em Padrões GoF.
Ação: Subam as alterações para o GitHub.
Bash
git add .
git commit -m "Refactor: Implementação do MVP e injeção de dependência na View"
git push origin refactor/mvp

Em seguida, entrem no GitHub de vocês, abram um Pull Request para a branch main e realizem o Merge.<?php
class TaskPresenter {
    private $model;
    private $view;

    public function __construct(Task $model, TaskViewInterface $view) {
        $this->model = $model;
        $this->view = $view;
    }

    public function index() {
        try {
            $tasks = $this->model->getAll();

            foreach ($tasks as &$task) {
                $task['title'] = strtoupper($task['title']);
            }
            unset($task);

            $this->view->displayTasks($tasks);
        } catch (Exception $e) {
            $this->view->showError($e->getMessage());
        }
    }

    public function create($title, $description, $dueDate) {
        try {
            $this->model->save($title, $description, $dueDate);
            header("Location: index.php");
        } catch (Exception $e) {
            $this->view->showError($e->getMessage());
        }
    }

    public function complete($id) {
        $this->model->complete($id);
        header("Location: index.php");
    }

    public function delete($id) {
        $this->model->delete($id);
        header("Location: index.php");
    }
}
