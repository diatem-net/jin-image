<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Image\Filters;

/**
 * Filtre image. Applique un effet de flou gaussien.
 */
final class Blur extends AbstractFilter
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
    imagefilter($imageRessource, IMG_FILTER_GAUSSIAN_BLUR);
    return $imageRessource;
  }

}