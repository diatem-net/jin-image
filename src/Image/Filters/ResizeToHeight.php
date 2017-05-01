<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Image\Filters;

use Jin2\Image\Image;

/**
 * Filtre image. Permet de redimentionner une image sans déformation en spécifiant une hauteur fixe de sortie. La largeur sera variable
 */
final class ResizeToHeight extends AbstractFilter
{

  /**
   * @var integer Hauteur souhaitée
   */
  private $height;

  /**
   * Constructeur
   *
   * @param integer $height  Hauteur souhaitée
   */
  public function __construct($height)
  {
    parent::__construct();
    $this->height = $height;
  }

  /**
   * Application du filtre
   *
   * @param  resource $imageRessource  ImageRessource GD sur lequel appliquer le filtre
   * @return resource                  ImageRessource GD modifiée
   */
  public function apply($imageRessource)
  {
    $startWidth = imagesx($imageRessource);
    $startHeight = imagesy($imageRessource);

    $yratio = $startHeight / $this->height;

    $newHeight = $this->height;
    $newWidth  = $startWidth * $newHeight / $startHeight;

    $resized = Image::createEmptyContainer($newWidth, $newHeight);
    imagecopyresampled($resized, $imageRessource, 0, 0, 0, 0, $newWidth, $newHeight, $startWidth, $startHeight);
    imagedestroy($imageRessource);
    return $resized;
  }

}
