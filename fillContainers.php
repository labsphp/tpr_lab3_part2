<?php
/**
 * Created by PhpStorm.
 * User: Serhii
 * Date: 12.04.2018
 * Time: 9:33
 */


include_once "vendor/autoload.php";

$itemsSize = $_POST['items'];
$weights = preg_split('#\s+#', $itemsSize);
$algorithmType = trim($_POST['algorithmType']);
$type = $_POST['type'];

$algorithm = new FillContainersAlgorithms();
foreach ($weights as $size) {
    $item = new Item($size);
    $algorithm->addItem($item);
}
if ($algorithmType == 'NFA' && $type == "unsorted") {
    list('countContainers' => $countContainers, 'countComparisons' => $countComparisons) = $algorithm->NFAUnsorted();
} elseif ($algorithmType == 'FFA' && $type == "unsorted") {
    list('countContainers' => $countContainers, 'countComparisons' => $countComparisons) = $algorithm->FFAUnsorted();
} elseif ($algorithmType == 'WFA' && $type == "unsorted") {
    list('countContainers' => $countContainers, 'countComparisons' => $countComparisons) = $algorithm->WFAUnsorted();
} elseif ($algorithmType == 'BFA' && $type == "unsorted") {
    list('countContainers' => $countContainers, 'countComparisons' => $countComparisons) = $algorithm->BFAUnsorted();
} elseif ($algorithmType == 'NFA' && $type == "sorted") {
    list('countContainers' => $countContainers, 'countComparisons' => $countComparisons) = $algorithm->NFASorted();
} elseif ($algorithmType == 'FFA' && $type == "sorted") {
    list('countContainers' => $countContainers, 'countComparisons' => $countComparisons) = $algorithm->FFASorted();
} elseif ($algorithmType == 'WFA' && $type == "sorted") {
    list('countContainers' => $countContainers, 'countComparisons' => $countComparisons) = $algorithm->WFASorted();
} elseif ($algorithmType == 'BFA' && $type == "sorted") {
    list('countContainers' => $countContainers, 'countComparisons' => $countComparisons) = $algorithm->BFASorted();
}
//Получаем список контейнеров
$listContainers = $algorithm->getListContainers();
$list = [];
foreach ($listContainers as $container) {
    $items = [];
    foreach ($container->getItems() as $item) {
        $items[] = $item->getItemSize();
    }
    $list[] = $items;
}
//min необходимое число контейнеров для упаковки груза
$minCountContainers = $algorithm->minCountContainers();

echo json_encode(['countContainers' => $countContainers, 'countComparisons' => $countComparisons,
    'listContainers' => $list, 'minCountContainers' => $minCountContainers]);


