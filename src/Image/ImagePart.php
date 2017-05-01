<?php

/**
 * Jin FrameWork
 */

namespace Jin2\Image;

/**
 * Classe permettant d'exploiter une portion d'un objet Image Jin.
 */
class ImagePart
{

  /**
   * @var resource  Ressource image contenant la portion coupée
   */
  private $cuttedRessource;

  /**
   * @var Image  Image source
   */
  private $srcImage;

  /**
   * Constructeur
   *
   * @param integer $x       Coordonnée X du point supérieur gauche où débuter la découpe
   * @param integer $y       Coordonnée Y du point supérieur gauche où débuter la découpe
   * @param integer $width   Largeur (en pixels) de la zone à découper
   * @param integer $height  Hauteur (en pixels) de la zone à découper
   * @param Image   $image   Objet image source
   */
  public function __construct($x, $y, $width, $height, Image $image)
  {
    $this->srcImage = $image;
    $this->cuttedRessource = $image->getEmptyContainer($width, $height);
    imagecopy($this->cuttedRessource, $image->getImageRessource(), 0, 0, $x, $y, $width, $height);
  }

  /**
   * Ecrit la portion d'image dans un fichier
   *
   * @param string $path  Chemin du fichier
   * @throws \Exception
   */
  public function write($path)
  {
    if ($this->srcImage->getExtension() == Image::IMAGE_TYPE_JPG) {
      imagejpeg($this->cuttedRessource, $path, $this->srcImage->getJpgQuality());
    } else if ($this->srcImage->getExtension() == Image::IMAGE_TYPE_PNG) {
      imagepng($this->cuttedRessource, $path, $this->srcImage->getPngCompression());
    } else {
      throw new \Exception('Impossible de générer l\'image : extension non supportée');
    }
  }

  /**
   * Retourne la ressource image resultante
   *
   * @return resource
   */
  public function getRessource()
  {
    return $this->cuttedRessource;
  }

}
