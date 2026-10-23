<?php

$root = dirname(__DIR__, 2);
$case = $argv[1];
require $root . '/tests/bootstrap.php';
require $root . '/tests/Support/BaseSubjects.php';
ee()->input = new \LowTitleTests\Input;
ee()->services['CP/URL'] = new \LowTitleTests\UrlFactory;
$pagination = new \LowTitleTests\Pagination;
foreach (array('perPage', 'currentPage', 'displayPageLinks', 'queryStringVariable') as $method) {
    $pagination->returns[$method] = $pagination;
}
$pagination->returns['render'] = '<pagination>';
$totals = array();
ee()->services['CP/Pagination'] = function ($total) use ($pagination, &$totals) {
    $totals[] = $total;

    return $pagination;
};
$mcp = (new ReflectionClass(\LowTitleTests\McpSubject::class))->newInstanceWithoutConstructor();
$mcp->flux = new \Low\Title\FluxCapacitor\FluxCapacitor;
$model = new \LowTitleTests\ModelQuery;

class ModelPaginationController
{
    /**
     * Supply the ordinary controller call frame for a nullable pagination URL.
     *
     * @param LowTitleTests\McpSubject $mcp
     * @param LowTitleTests\ModelQuery $model
     * @return LowTitleTests\ModelQuery
     */
    public function entries($mcp, $model)
    {
        return $mcp->paginateModel($model);
    }
}

try {
    $result = null;
    if ($case === 'named-default') {
        $result = (new ModelPaginationController)->entries($mcp, $model);
    }
    if ($case === 'global-explicit') {
        $result = $mcp->paginateModel($model, new \LowTitleTests\Url('entries'));
    }
    if ($case === 'global-default') {
        $result = $mcp->paginateModel($model);
    }
    echo json_encode(array('ok' => true, 'same_model' => $result === $model, 'model_calls' => $model->calls, 'data' => $mcp->data, 'pagination_calls' => $pagination->calls, 'totals' => $totals));
} catch (Throwable $exception) {
    $trace = array_map(function ($frame) {
        return array(isset($frame['file']) ? $frame['file'] : null, isset($frame['line']) ? $frame['line'] : null);
    }, $exception->getTrace());
    echo json_encode(array('ok' => false, 'class' => get_class($exception), 'message' => $exception->getMessage(), 'file' => $exception->getFile(), 'line' => $exception->getLine(), 'trace' => $trace));
    exit(1);
}
