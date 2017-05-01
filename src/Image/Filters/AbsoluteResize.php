<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Image\Filters;

use Jin2\Image\Image;

/**
 * Filtre image. Permet de redimentionner une image de manière absolue, c'est à dire sans garantie de conservation du ration largeur/hauteur
 */
final class AbsoluteResize extends AbstractFilter
{

  /**
   * @var integer Largeur souhaitée
   */
  private $width;

  /**
   * @var integer Hauteur souhaitée
   */
  private $height;

  /**
   * Constructeur
   *
   * @param integer $width   Largeur souhaitée
   * @param integer $height  Hauteur souhaitée
   */
  public function __construct($width, $height)
  {
    parent::__construct();
    $this->width = $width;
    $this->height = $height;
  }

  /**
   * Application du filtre
   * @param resource $imageRessource  ImageRessource GD sur lequel appliquer le filtre
   * @return resource  ImageRessource GD modifié
   */
  public function apply($imageRessource)
  {
    $startWidth = imagesx($imageRessource);
    $startHeight = imagesy($imageRessource);

    if($startWidth == $this->width && $startHeight == $this->height) {
      // Cas particulier. Image déjà à la bonne taille.
      return $imageRessource;
    }

    $resized = Image::createEmptyContainer($this->width, $this->height);
    imagecopyresampled($resized, $imageRessource, 0, 0, 0, 0, $this->width, $this->height, $startWidth, $startHeight);
    imagedestroy($imageRessource);
    return $resized;
  }

}