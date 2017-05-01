<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Image\Filters;

/**
 * Classe parent de tous les filtres Image
 */
abstract class AbstractFilter
{

  /**
   * @var Image Instance de la classe Image sur lequel appliquer le filtre
   */
  protected $image;

  /**
   * Constructeur
   */
  public function __construct()
  {
  }

  /**
   * Methode appelée pour appliquer le filtre sur l'objet Image
   *
   * @param  resource $imageRessource  ImageRessource GD sur lequel appliquer le filtre
   * @return resource                  ImageRessource GD modifiée
   */
  abstract public function apply($imageRessource);

}

