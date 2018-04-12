<?php

/**
 * Created by PhpStorm.
 * User: Serhii
 * Date: 10.04.2018
 * Time: 23:30
 */
class Container
{
    //вместимость контейнера
    private $containerCapacity = 100;
    //заполненность контейнера
    private $containerSize = 0;
    //Массив элементов в контейнере
    private $items = [];

    /**
     *
     * @return int
     */
    public function getContainerCapacity():int
    {
        return $this->containerCapacity;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }


    /**
     * @return mixed
     */
    public function getContainerSize()
    {
        return $this->containerSize;
    }


    //Добавление элемента в контейнер
    public function addItem(Item $item)
    {
        $this->items[] = $item;
    }

    //Изменение заполненности конейнера
    public function updateContainerSize(int $size)
    {
        $this->containerSize += $size;
    }

    //Получение свободного места в контейнере
    public function getFreeContainerCapacity():int
    {
        return $this->containerCapacity - $this->containerSize;
    }
}