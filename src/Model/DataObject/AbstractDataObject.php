<?php

namespace App\Vote\Model\DataObject;

abstract class AbstractDataObject
{
    /**
     * Retourne un Objet en tableau de données
     * @return array
     */
    public abstract function formatTableau(): array;
}
