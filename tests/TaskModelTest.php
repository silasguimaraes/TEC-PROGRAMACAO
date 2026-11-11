<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Model/Task.php';

class TaskModelTest extends TestCase {
    public function testNaoPodeSalvarTarefaSemTitulo() {
        $pdoStub = $this->createStub(PDO::class);
        $model = new Task($pdoStub);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Título e Data são obrigatórios.");

        $model->save("", "Fazer compras", "2026-12-31");
    }
}
