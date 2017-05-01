<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Image\Filters;

/**
 * Filtre image. Permet de dessiner dans l'image un rectangle
 */
final class RectangleFill extends AbstractFilter
{

  /**
   * @var int  Coordonnées X du point 1
   */
  private $x1;

  /**
   * @var int  Coordonnées Y du point 1
   */
  private $y1;

  /**
   * @var int  Coordonnées X du point 2
   */
  private $x2;

  /**
   * @var int  Coordonnées Y du point 2
   */
  private $y2;

  /**
   * @var int  Couleur. Composante rouge.
   */
  private $r;

  /**
   * @var int  Couleur. Composante verte.
   */
  private $g;

  /**
   * @var int  Couleur. Composante bleue.
   */
  private $b;

  /**
   * Constructeur
   *
   * @param int $x1   Coordonnées X du point 1
   * @param int $y1   Coordonnées Y du point 1
   * @param int $x2   Coordonnées X du point 2
   * @param int $y2   Coordonnées Y du point 2
   * @param int $r    Couleur de remplissage. Composante rouge.
   * @param int $g    Couleur de remplissage. Composante verte.
   * @param int $b    Couleur de remplissage. Composante bleue.
   */
  public function __construct($x1, $y1, $x2, $y2 , $r, $g, $b)
  {
      parent::__construct();
      $this->b = $b;
      $this->g = $g;
      $this->r = $r;
      $this->x1 = $x1;
      $this->x2 = $x2;
      $this->y1 = $y1;
      $this->y2 = $y2;
  }

  /**
   * Application du filtre
   *
   * @param  resource $imageRessource  ImageRessource GD sur lequel appliquer le filtre
   * @return resource                  ImageRessource GD modifiée
   */
  public function apply($imageRessource)
  {
    $color = imagecolorallocate($imageRessource, $this->r, $this->g, $this->b);
    imagefilledrectangle($imageRessource, $this->x1, $this->y1, $this->x2, $this->y2, $color);
    return $imageRessource;
  }

}
