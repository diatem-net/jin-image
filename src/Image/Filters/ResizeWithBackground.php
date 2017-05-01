<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Image\Filters;

use Jin2\Image\Image;

/**
 * Filtre image. Permet de redimentionner une image sans déformation.
 * L'image en sortie fera exactement la taille indiquée. La matière manquante
 * sera remplacée par la couleur indiquée. (Dans le cas d'une image avec
 * transparence il est possible de ne pas renseigner les paramètres de couleur
 * pour obtenir un fond transparent)
 */
final class ResizeWithBackground extends AbstractFilter
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
   * @var integer Composante rouge de la couleur de fond. NULL = transparent
   */
  private $red;

  /**
   * @var integer Composante verte de la couleur de fond. NULL = transparent
   */
  private $green;

  /**
   * @var integer Composante bleue de la couleur de fond. NULL = transparent
   */
  private $blue;

  /**
   * Constructeur
   *
   * @param integer $width   Largeur souhaite
   * @param integer $height  Hauteur souhaitée
   * @param integer $red     (optional) Composante rouge de la couleur de fond. (De 0 à 100) NULL = transparent
   * @param integer $green   (optional) Composante verte de la couleur de fond. (De 0 à 100) NULL = transparent
   * @param integer $blue    (optional) Composante bleue de la couleur de fond. (De 0 à 100) NULL = transparent
   */
  public function __construct($width, $height, $red = null, $green = null, $blue = null)
  {
    parent::__construct();
    $this->width = $width;
    $this->height = $height;
    $this->red = $red;
    $this->green = $green;
    $this->blue = $blue;
  }

  /**
   * Methode appelée pour appliquer le filtre sur l'objet Image
   *
   * @param  resource $imageRessource  ImageRessource GD sur lequel appliquer le filtre
   * @return resource                  ImageRessource GD modifiée
   */
  public function apply($imageRessource)
  {
    $startWidth = imagesx($imageRessource);
    $startHeight = imagesy($imageRessource);

    $xratio = $startWidth / $this->width;
    $yratio = $startHeight / $this->height;
    if ($xratio == $yratio) {
      if ($startHeight == $this->height && $startWidth == $this->width) {
        return $imageRessource;
      }
      $newWidth  = $this->width;
      $newHeight = $this->height;
    } else if($xratio > $yratio) {
      $newWidth  = $this->width;
      $newHeight = (($startHeight*(($newWidth)/$startWidth)));
    } else {
      $newHeight = $this->height;
      $newWidth  = (($startWidth*(($newHeight)/$startHeight)));
    }

    // Calcul du décalage de l'image
    $xdecay = 0;
    if ($newWidth != $this->width) {
      $xdecay = ($this->width - $newWidth)/2;
    }
    $ydecay = 0;
    if ($newHeight != $this->height) {
      $ydecay = ($this->height - $newHeight)/2;
    }

    $resized = Image::createEmptyContainer($this->width, $this->height, $this->red, $this->green, $this->blue, true);
    imagecopyresampled($resized, $imageRessource, $xdecay, $ydecay, 0, 0, $newWidth, $newHeight, $startWidth, $startHeight);
    imagedestroy($imageRessource);
    return $resized;
  }

}
