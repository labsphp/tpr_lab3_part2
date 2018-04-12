<?php

/**
 * Created by PhpStorm.
 * User: Serhii
 * Date: 10.04.2018
 * Time: 23:40
 */
class FillContainersAlgorithms
{
    private $listItems = [];
    private $listContainers = [];
    private $currentContainer;

    public function __construct()
    {
        $this->currentContainer = new Container();
        $this->listContainers[] = $this->currentContainer;
    }

    //добавляем вещи в список вещей
    public function addItem(Item $item)
    {
        $this->listItems[] = $item;
    }

    /**
     * @param array $listItems
     */
    public function setListItems(array $listItems)
    {
        $this->listItems = $listItems;
    }

    /**
     * @return array
     */
    public function getListItems(): array
    {
        return $this->listItems;
    }


    /**
     * @return array
     */
    public function getListContainers(): array
    {
        return $this->listContainers;
    }

    /**
     * Сортировка списка грузов в порядке убывания
     */
    private function sortListItems():void
    {
        $listItems = $this->getListItems();
        usort($listItems, function (Item $a, Item $b) {
            $aCost = $a->getItemSize();
            $bCost = $b->getItemSize();
            if ($aCost == $bCost) return 0;
            return $aCost > $bCost ? -1 : 1;
        });
        $this->setListItems($listItems);
        return;
    }

    //NFA без упорядочивания
    public function NFAUnsorted():array
    {
        $countComparisons = 0;
        foreach ($this->listItems as $item) {
            //Определяем размер элемента
            $itemSize = $item->getItemSize();
            //Находим размер свободного места в текущем контейнере
            $freeContainerCapacity = $this->currentContainer->getFreeContainerCapacity();
            //Если есвть свободное место
            if ($freeContainerCapacity >= $itemSize) {
                $countComparisons++;
                //Добавляем элемент в контейнер и обновляем заполненность контейнера
                $this->currentContainer->addItem($item);
                $this->currentContainer->updateContainerSize($itemSize);
            } else {
                $countComparisons++;
                //Создаем новый
                $this->createContainer($item, $itemSize);
            }
        }
        return ['countContainers' => count($this->listContainers), 'countComparisons' => $countComparisons];
    }

    //NFA c упорядочиванием
    public function NFASorted():array
    {
        $this->sortListItems();
        return $this->NFAUnsorted();
    }


    //FFA без упорядочивания
    public function FFAUnsorted():array
    {
        $countComparisons = 0;
        foreach ($this->listItems as $item) {
            //Определяем размер элемента
            $itemSize = $item->getItemSize();
            //Находим размер свободного места в текущем контейнере
            $freeContainerCapacity = $this->currentContainer->getFreeContainerCapacity();

            if ($freeContainerCapacity >= $itemSize) {
                $countComparisons++;
                $this->currentContainer->addItem($item);
                $this->currentContainer->updateContainerSize($itemSize);
            } else {
                $countComparisons++;
                $done = false;
                foreach ($this->listContainers as $container) {
                    $freeContainerCapacity = $container->getFreeContainerCapacity();
                    if ($freeContainerCapacity >= $itemSize) {
                        $countComparisons++;
                        $container->addItem($item);
                        $done = true;
                        break;
                    }
                }
                if (!$done) {
                    $countComparisons++;
                    //Создаем новый
                    $this->createContainer($item, $itemSize);
                }

            }
        }
        return ['countContainers' => count($this->listContainers), 'countComparisons' => $countComparisons];
        //return count($this->listContainers);
    }

    //FFA c упорядочиванием
    public function FFASorted():array
    {
        $this->sortListItems();
        return $this->FFAUnsorted();
    }

    //WFA без упорядочивания
    public function WFAUnsorted():array
    {
        $countComparisons = 0;
        foreach ($this->listItems as $item) {
            //Определяем размер элемента
            $itemSize = $item->getItemSize();
            //Находим размер свободного места в текущем контейнере
            $freeContainerCapacity = $this->currentContainer->getFreeContainerCapacity();
            if ($freeContainerCapacity >= $itemSize) {
                $countComparisons++;
                $this->currentContainer->addItem($item);
                $this->currentContainer->updateContainerSize($itemSize);
            } else {
                $countComparisons++;
                $maxFreeCapacity = PHP_INT_MIN;
                $this->currentContainer = null;
                foreach ($this->listContainers as $container) {
                    //Находим контейнер с наибольшим код-вом свободного места
                    $curContainerFreeCapacity = $container->getFreeContainerCapacity();
                    if ($curContainerFreeCapacity > $maxFreeCapacity) {
                        $countComparisons++;
                        $maxFreeCapacity = $curContainerFreeCapacity;
                        $this->currentContainer = $container;
                    }
                }
                //Если груз влазит в контейнер, то добавляем его туда
                if ($maxFreeCapacity >= $itemSize) {
                    $countComparisons++;
                    $this->currentContainer->addItem($item);
                    $this->currentContainer->updateContainerSize($itemSize);
                    //Назначаем текущим контейнером самый поседний в списке
                    $this->currentContainer = end($this->listContainers);
                } else {
                    $countComparisons++;
                    //Создаем новый
                    $this->createContainer($item, $itemSize);
                }
            }
        }
        return ['countContainers' => count($this->listContainers), 'countComparisons' => $countComparisons];
    }

    //WFA c упорядочиванием
    public function WFASorted():array
    {
        $this->sortListItems();
        return $this->WFAUnsorted();
    }

    //BFA без упорядочивания
    public function BFAUnsorted():array
    {
        $countComparisons = 0;
        foreach ($this->listItems as $item) {
            $itemSize = $item->getItemSize();
            $freeContainerCapacity = $this->currentContainer->getFreeContainerCapacity();
            if ($freeContainerCapacity >= $itemSize) {
                $countComparisons++;
                $this->currentContainer->addItem($item);
                $this->currentContainer->updateContainerSize($itemSize);
            } else {
                $countComparisons++;
                $minFreeCapacity = PHP_INT_MAX;
                $this->currentContainer = null;
                //Находим максимально заполненный контейнер, который способен вместить данный груз
                foreach ($this->listContainers as $container) {
                    $curContainerFreeCapacity = $container->getFreeContainerCapacity();
                    if ($curContainerFreeCapacity < $minFreeCapacity && $curContainerFreeCapacity >= $itemSize) {
                        $countComparisons++;
                        $minFreeCapacity = $curContainerFreeCapacity;
                        $this->currentContainer = $container;
                    }
                }
                //если нашли подходящий контейнер, то добавим в него груз
                if (!is_null($this->currentContainer)) {
                    $countComparisons++;
                    $this->currentContainer->addItem($item);
                    $this->currentContainer->updateContainerSize($itemSize);
                    $this->currentContainer = end($this->listContainers);
                } else {
                    //Создаем новый
                    $countComparisons++;
                    $this->createContainer($item, $itemSize);
                }
            }
        }
        return ['countContainers' => count($this->listContainers), 'countComparisons' => $countComparisons];
    }

    //FFA c упорядочиванием
    public function BFASorted():array
    {
        $this->sortListItems();
        return $this->BFAUnsorted();
    }

    //Создание нового контейнера
    private function createContainer(Item $item, int $itemSize):void
    {
        $this->currentContainer = new Container();
        $this->currentContainer->addItem($item);
        $this->currentContainer->updateContainerSize($itemSize);
        $this->listContainers[] = $this->currentContainer;
        return;
    }

    /**
     * Расчет минимального колличества контйнеров, необходимых для упаковки груза
     * @return int
     */
    public function minCountContainers():int
    {
        $sum = 0;
        foreach ($this->listItems as $item) {
            $sum += $item->getItemSize();
        }
        return ceil($sum / $this->currentContainer->getContainerCapacity());
    }

}