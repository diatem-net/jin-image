<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Image;

use Jin2\Image\Filters\AbstractFilter;

/**
 * Classe permettant la modification d'images via des filtres.
 * On peut extraire l'image générée de plusieurs méthodes :
 * -> Modification du fichier original (write())
 * -> Ecriture dans un nouveau fichier (write($path))
 * -> Sortie en tant qu'image dans le navigateur (writeInOutput())
 * -> Sortie dans le navigateur en HTML (writeInHTMLOutput())
 * -> Sortie du binaire
 */
class Image
{

  const IMAGE_TYPE_JPG = 'jpg';
  const IMAGE_TYPE_PNG = 'png';

  /**
   * @var string  Chemin du fichier
   */
  protected $path;

  /**
   * @var ressource  Ressource vide temporaire
   */
  protected $emptyRessource;

  /**
   * @var ressource  Ressource image après traitement
   */
  protected $buildedRessource;

  /**
   * @var string  Extension du fichier. (lowercase)
   */
  protected $extension;

  /**
   * @var array   Filtres applicables. (Tableau d'AbstractFilter)
   */
  protected $filters = array();

  /**
   * @var integer Qualité appliquée à l'écriture des fichiers de type JPEG
   */
  protected $jpgQuality = 100;

  /**
   * @var integer Degré de compression appliqué à l'écriture des fichiers de type PNG
   */
  protected $pngCompression = 0;

  /**
   * Crée un objet Image à partir d'une ressouce GD
   *
   * @param  string  $gdResource   Ressource GD
   * @param  boolean $transparency Gestion de la transparence ou non
   * @return Image                 Objet Image créé
   */
  public static function getImageObjectFromGDResource($gdResource, $transparency = true)
  {
    $image = new Image(null, imagesx($gdResource), imagesy($gdResource));
    $image->setGdResource($gdResource);
    return $image;
  }

  /**
   * Crée un objet Image à partir d'un fichier temporaire généré par un formulaire.
   * Ces fichiers sont sans extensions, on ne peut pas passer par le constructeur standart.
   * Note : on ne devrait pas à avoir à utiliser cette fonction dans le cadre d'une utilisation de DForm.
   *
   * @param  string $tmpFile Chemin du fichier temporaire
   * @return Image           Objet Image créé
   */
  public static function getImageObjectFromTmpFile($tmpFile, $typeFile = self::IMAGE_TYPE_JPG)
  {
    $image = new Image();
    $image->setTmpFile($tmpFile, $typeFile);
    return $image;
  }

  /**
   * Constructeur
   *
   * @param string $path Chemin d'un fichier existant. Si NULL construction d'une image vide.
   * @throws \Exception
   */
  public function __construct($path = null, $width = null, $height = null, $red = null, $green = null, $blue = null, $transparency = true)
  {
    if ($path) {
      $this->path = $path;

      if ($this->path != '') {
        $parts = explode('.', $this->path);
        $this->extension = strtolower($parts[count($parts) - 1]);
      }
      if ($this->extension == 'jpeg') {
        $this->extension = self::IMAGE_TYPE_JPG;
      }
      if ($this->extension != self::IMAGE_TYPE_JPG && $this->extension != self::IMAGE_TYPE_PNG) {
        throw new \Exception('Extension '.$this->extension.' non supportée');
      }
    } else {
      if (!is_null($red) && !is_null($green) && !is_null($blue) && !$transparency) {
        $this->extension = self::IMAGE_TYPE_JPG;
        $this->emptyRessource = static::createEmptyContainer($width, $height, $red, $green, $blue, $transparency);
      } else {
        $this->extension = self::IMAGE_TYPE_PNG;
        if (!is_null($red) && !is_null($green) && !is_null($blue)) {
          $this->emptyRessource = static::createEmptyContainer($width, $height, $red, $green, $blue, $transparency);
        } else {
          $this->emptyRessource = static::createEmptyContainer($width, $height, null, null, null, $transparency);
        }
      }
    }
  }

  /**
   * Ajoute un filtre de traitement
   *
   * @param AbstractFilter $filter Filtre de traitement
   */
  public function addFilter(AbstractFilter $filter)
  {
    $this->filters[] = $filter;
  }

  /**
   * Ecrit le résultat dans un fichier
   *
   * @param  string $path  (optional) Fichier de destination. Si non renseigné : fichier d'origine.
   * @throws \Exception
   */
  public function write($path = null)
  {
    $image = $this->applyFilters();

    if ($this->extension == self::IMAGE_TYPE_JPG) {
      if ($path) {
        imagejpeg($image, $path, $this->jpgQuality);
      } else if ($this->path) {
        imagejpeg($image, $this->path, $this->jpgQuality);
      } else {
        throw new \Exception('Aucun fichier de sortie configuré.');
      }
    } else if ($this->extension == self::IMAGE_TYPE_PNG) {
      if ($path) {
        imagepng($image, $path, $this->pngCompression);
      } else if ($this->path) {
        imagepng($image, $this->path, $this->pngCompression);
      } else {
        throw new \Exception('Aucun fichier de sortie configuré.');
      }
    } else {
      throw new \Exception('Impossible de générer l\'image : extension non supportée');
    }
  }

  /**
   * Retourne une portion d'image
   *
   * @param  integer $x       Coordonnée X du point supérieur gauche où débuter la découpe
   * @param  integer $y       Coordonnée Y du point supérieur gauche où débuter la découpe
   * @param  integer $width   Largeur (en pixels) de la zone à découper
   * @param  integer $height  Hauteur (en pixels) de la zone à découper
   * @return ImagePart
   */
  public function getImagePart($x, $y, $width, $height)
  {
    return new ImagePart($x, $y, $width, $height, $this);
  }

  /**
   * Retourne la largeur de l'image
   *
   * @return integer
   * @throws \Exception
   */
  public function getWidth()
  {
    if (!$this->buildedRessource) {
      throw new \Exception('Il est nécessaire d\'appliquer les filtres au préalable.');
    }
    return imagesx($this->buildedRessource);
  }

  /**
   * Retourne la hauteur de l'image
   *
   * @return integer
   * @throws \Exception
   */
  public function getHeight()
  {
    if (!$this->buildedRessource) {
      throw new \Exception('Il est nécessaire d\'appliquer les filtres au préalable.');
    }
    return imagesy($this->buildedRessource);
  }

  /**
   * Effectue une sortie de l'image directement dans le navigateur. (Headers modifiés)
   *
   * @throws \Exception
   */
  public function writeInOutput()
  {
    $image = $this->applyFilters();

    if ($this->extension == self::IMAGE_TYPE_JPG) {
      header('Content-Type: image/jpg');
      imagejpeg($image, null, $this->jpgQuality);
    } else if ($this->extension == self::IMAGE_TYPE_PNG) {
      header('Content-Type: image/png');
      imagepng($image, null, $this->pngCompression);
    } else {
      throw new \Exception('Impossible de générer l\'image : extension non supportée');
    }
  }

  /**
   * Effectue une sortie de l'image en HTML. (balise img et base64)
   *
   * @throws \Exception
   */
  public function writeInHTMLOutput()
  {
    $image = $this->applyFilters();

    if ($this->extension == self::IMAGE_TYPE_JPG) {
      ob_start();
      imagejpeg($image, null, $this->jpgQuality);
      $contents = ob_get_contents();
      ob_end_clean();

      $base64 = "data:image/jpeg;base64," . base64_encode($contents);
      echo "<img src=$base64 />";
    } else if ($this->extension == self::IMAGE_TYPE_PNG) {
      ob_start();
      imagepng($image, null, $this->pngCompression);
      $contents = ob_get_contents();
      ob_end_clean();

      $base64 = "data:image/png;base64," . base64_encode($contents);
      echo "<img src=$base64 />";
    } else {
      throw new \Exception('Impossible de générer l\'image : extension non supportée');
    }
  }

  /**
   * Retourne l'image générée en base64
   *
   * @return string
   * @throws \Exception
   */
  public function getBase64()
  {
    $image = $this->applyFilters();

    if ($this->extension == self::IMAGE_TYPE_JPG) {
      ob_start();
      imagejpeg($image, null, $this->jpgQuality);
      $contents = ob_get_contents();
      ob_end_clean();

      $base64 = base64_encode($contents);
      return $base64;
    } else if ($this->extension == self::IMAGE_TYPE_PNG) {
      ob_start();
      imagepng($image, null, $this->pngCompression);
      $contents = ob_get_contents();
      ob_end_clean();

      $base64 = base64_encode($contents);
      return $base64;
    } else {
      throw new \Exception('Impossible de générer l\'image : extension non supportée');
    }
  }

  /**
   * Retourne l'image générée en objet RessourceImage GD
   *
   * @return resource RessourceImage GD
   */
  public function getImageRessource()
  {
    if (!$this->buildedRessource) {
      $this->buildedRessource = $this->applyFilters();
    }
    return $this->buildedRessource;
  }

  /**
   * Modifie la qualité de sortie des fichiers de type JPEG
   *
   * @param integer $quality  Qualité. De 0 à 100
   * @throws \Exception
   */
  public function setJpegQuality($quality)
  {
    if (!is_numeric($quality) ||
      $quality < 0 ||
      $quality > 100) {
      throw new \Exception('Qualité JPEG : valeur numérique attendue de 0 à 100');
    }
    $this->jpgQuality = $quality;
  }

  /**
   * Modifié le degré de compression appliqué aux fichiers de type PNG*
   *
   * @param integer $compression  Degré de compression. (De 0 à 9)
   * @throws \Exception
   */
  public function setPngCompression($compression)
  {
    if (!is_numeric($compression) ||
      $compression < 0 ||
      $compression > 9) {
      throw new \Exception('Compression PNG : valeur numérique attendue de 0 à 9');
    }
    $this->jpgQuality = $quality;
  }

  /**
   * Retourne TRUE si le fichier supporte la transparence
   *
   * @return boolean
   */
  public function isTransparency()
  {
    return $this->extension == self::IMAGE_TYPE_PNG;
  }

  /**
   * Retourne l'extension de l'image
   *
   * @return string
   */
  public function getExtension()
  {
    return $this->extension;
  }

  /**
   * Retourne la qualité JPG (de 0 à 100) (si fichier de type JPG)
   *
   * @return integer
   */
  public function getJpgQuality()
  {
    return $this->jpgQuality;
  }

  /**
   * Retourne le degré de compression. (de 0 à 9) (Si fichier de type PNG)
   *
   * @return integer
   */
  public function getPngCompression()
  {
    return $this->pngCompression;
  }

  /**
   * Crée une image container vide
   *
   * @param  integer $width        Largeur
   * @param  integer $height       Hauteur
   * @param  integer $red          (optional) Composante rouge de la couleur de fond. Si non renseigné : couleur transparente.
   * @param  integer $green        (optional) Composante verte de la couleur de fond. Si non renseigné : couleur transparente.
   * @param  integer $blue         (optional) Composante bleue de la couleur de fond. Si non renseigné : couleur transparente.
   * @param  boolean $transparency (optional) Composante bleue de la couleur de fond. Si non renseigné : couleur transparente.
   * @return resource
   */
  public static function createEmptyContainer($width, $height, $red = null, $green = null, $blue = null, $transparency = false)
  {
    $container = imagecreatetruecolor($width, $height);
    if ($transparency) {
      imagesavealpha($container, true);
      $trans_colour = imagecolorallocatealpha($container, 0, 0, 0, 127);
      imagefill($container, 0, 0, $trans_colour);
    }
    if (!is_null($red) && !is_null($green) && !is_null($blue)) {
      $color = imagecolorallocate($container, $red, $green, $blue);
      imagefill($container, 0, 0, $color);
    }
    return $container;
  }

  /**
   * Applique les filtres et retourne un objet ResourceImage GD
   *
   * @return resource
   */
  protected function applyFilters()
  {
    if ($this->emptyRessource) {
      $source = $this->emptyRessource;
    } else if ($this->extension == self::IMAGE_TYPE_JPG) {
      $source = imagecreatefromjpeg($this->path);
    } else if ($this->extension == self::IMAGE_TYPE_PNG) {
      $source = imagecreatefrompng($this->path);
      imagealphablending($source, false);
      imagesavealpha($source, true);
    }
    foreach($this->filters as $filtre) {
      $source = $filtre->apply($source);
    }
    $this->buildedRessource = $source;
    return $source;
  }

  protected function setGdResource($gd)
  {
    $this->emptyRessource = $gd;
  }

  protected function setTmpFile($tmpFile, $typeFile)
  {
    $this->path = $tmpFile;
    $this->extension = $typeFile;

    if ($this->extension == 'jpeg') {
      $this->extension = self::IMAGE_TYPE_JPG;
    }
    if ($this->extension != self::IMAGE_TYPE_JPG && $this->extension != self::IMAGE_TYPE_PNG) {
      throw new \Exception('Extension '.$this->extension.' non supportée');
    }
  }

}
