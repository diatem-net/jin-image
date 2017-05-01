<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Image\Filters;

/**
 * Filtre image. Passe l'image en niveaux de gris
 */
final class Greyscale extends AbstractFilter
{

  /**
   * Constructeur
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Application du filtre
   *
   * @param  resource $imageRessource  ImageRessource GD sur lequel appliquer le filtre
   * @return resource                  ImageRessource GD modifiée
   */
  public function apply($imageRessource)
  {
    imagefilter($imageRessource, IMG_FILTER_GRAYSCALE);
    return $imageRessource;
  }

}