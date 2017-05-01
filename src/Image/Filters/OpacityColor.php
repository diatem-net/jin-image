<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Image\Filters;

/**
 * Filtre image. Applique un effet d'opacité sur une image tendant vers une couleur.
 * Remarque : principalement appliquable à des images JPEG
 */
final class OpacityColor extends AbstractFilter
{

  /**
   * @var integer Composante rouge de l'effet
   */
  private $red;

  /**
   * @var integer Composant verte de l'effet
   */
  private $green;

  /**
   * @var integer Composante bleue de l'effet
   */
  private $blue;

  /**
   * @var integer Opacité
   */
  private $opacity;

  /**
   * Constructeur
   *
   * @param integer $opacity  Opacité (de 0 à 100)
   * @param integer $red      Composante rouge de l'effet.
   * @param integer $green    Composante verte de l'effet.
   * @param integer $blue     Composante bleue de l'effet.
   */
  public function __construct($opacity, $red, $green, $blue)
  {
    parent::__construct();
    $this->opacity = 100 - $opacity;
    $this->red = $red;
    $this->green = $green;
    $this->blue = $blue;
  }

  /**
   * Application du filtre
   *
   * @param  resource $imageRessource  ImageRessource GD sur lequel appliquer le filtre
   * @return resource                  ImageRessource GD modifiée
   */
  public function apply($imageRessource)
  {
    $width = imagesx($imageRessource);
    $height = imagesy($imageRessource);
    for ($x = 0; $x < $width; $x++) {
      for ($y = 0; $y < $height; $y++) {
        $color = imagecolorallocatealpha($imageRessource, $this->red, $this->green, $this->blue, $this->opacity);
        imagesetpixel($imageRessource, $x, $y, $color);
      }
    }
    return $imageRessource;
  }

}
