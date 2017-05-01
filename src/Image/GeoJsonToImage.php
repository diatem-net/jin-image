<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Image;

use Jin2\FileSystem\File;

/**
 * Permet de transformer un fichier GeoJson en image PNG
 */
class GeoJsonToImage
{

  /**
   * @var array Données GeoJson
   */
  private $geoJsonArray;

  /**
   * @var array Coordonnées GPS min /max array('so' => array('lat' => float, 'lon' => float), 'ne' => array('lat' => float, 'lon' => float))
   */
  private $geoBoundsLatLong;

  /**
   * @var array Coordonnées en mètres min /max array('so' => array('xm' => int, 'ym' => int), 'ne' => array('xm' => int, 'ym' => int))
   */
  private $geoBoundsMeters;

  /**
   * @var int Nombre des formes tracées
   */
  private $shapesCount = 0;

  /**
   * @var int Taille w ou h minimum de l'image à générer
   */
  private $minSize;

  /**
   * @var int Largeur de l'image qui sera générée
   */
  private $outputWidth;

  /**
   * @var int Hauteur de l'image qui sera générée
   */
  private $outputheight;

  /**
   * @var string Format d'image (jpeg ou png)
   */
  private $imageFormat = Image::IMAGE_TYPE_JPG;

  /**
   * @var int Qualité d'image (de 0 à 100)
   */
  private $imageQuality;

  /**
   * @var string Couleur de fond. Si NULL transparent (#hexa)
   */
  private $bgColor;

  /**
   * @var string Couleur de remplissage par défaut des formes. (#hexa)
   */
  private $fillColor = '#000000';

  /**
   * @var string Si non NULL : attribut du GeoJson utilisé pour déterminer la couleur de remplissage de la forme
   */
  private $fillColorBasedOnAttribute;

  /**
   * @var array Si non NULL : tableau de correspondance entre les valeurs de l'attribut GeoJson (fillColorBasedOnAttribute) et une couleur.
   */
  private $fillColorBasedOnAttributeCorresp;

  /**
   * @var float Opacité de la couleur de remplissage (de 0 à 1)
   */
  private $fillColorOpacity = 1;

  /**
   * @var string Cible sur laquelle appeler la fonction de callback
   */
  private $drawShapeCallBackTarget;

  /**
   * @var string Fonction de callback à appeler après le traitement
   */
  private $drawShapeCallBackFunctionName;

  /**
   * @var string Couleur par défaut des lignes des formes. (#hexa)
   */
  private $strokeColor = '#000000';

  /**
   * @var float Opacité par défaut des lignes. de 0 à 1
   */
  private $strokeColorOpacity = 1;

  /**
   * @var int Largeur des lignes des tracés
   */
  private $strokeWidth = 0;

  /**
   * @var string Si non NULL : attribut du GeoJson utilisé pour déterminer la couleur des lignes de la forme
   */
  private $strokeColorBasedOnAttribute;

  /**
   * @var array Si non NULL : tableau de correspondance entre les valeurs de l'attribut GeoJson (strokeColorBasedOnAttribute) et une couleur.
   */
  private $strokeColorBasedOnAttributeCorresp;

  /**
   * Constructeur
   *
   * @param int    $minSize       Taille minimale en largeur et hauteur de l'image à générer. (Au minimum la largeur et la hauteur respecteront cette contrainte)
   * @param string $imageFormat   Format d'image. (jpg ou png)
   * @param int    $quality       Qualité d'image. (de 0 à 100)
   */
  public function __construct($minSize = 2000, $imageFormat = Image::IMAGE_TYPE_JPG, $quality = 100)
  {
    $this->minSize = $minSize;
    $this->setImageFormat($imageFormat);
    $this->setQuality($quality);
  }

  /**
   * Définit le GeoJson source à partir d'un fichier GeoJson
   *
   * @param string $geoJsonFilePath   Chemin relatif ou absolu du fichier
   */
  public function populateFromGeoJsonFile($geoJsonFilePath)
  {
    $file = new File($geoJsonFilePath);
    $this->geoJsonArray = $this->checkGeoJson($file->getContent());
    $this->analyse();
  }

  /**
   * Définit le GeoJson source à partir d'une chaîne
   *
   * @param string $geoJsonString     Chaîne au format geoJson
   */
  public function polulateFromGeoJsonString($geoJsonString)
  {
    $this->geoJsonArray = $this->checkGeoJson($geoJsonString);
    $this->analyse();
  }

  /**
   * Redéfinit la qualité de l'image en sortie
   *
   * @param int $quality  Qualité. de 0 à 100
   * @throws \Exception
   */
  public function setQuality($quality)
  {
    if (!is_numeric($quality) || $quality < 0 || $quality > 100) {
      throw new \Exception('La qualité doit être comprise entre 0 et 100');
    }
    $this->imageQuality = $quality;
  }

  /**
   * Modifie le format d'image attendu en sortie
   *
   * @param string $imageFormat   Format. jpg ou png
   * @throws \Exception
   */
  public function setImageFormat($imageFormat)
  {
    $this->imageFormat = $imageFormat;
    if ($this->imageFormat != Image::IMAGE_TYPE_JPG && $this->imageFormat != Image::IMAGE_TYPE_PNG) {
      throw new \Exception('Format ' . $this->imageFormat . ' non supporté !');
    }
  }

  /**
   * Définit la couleur de fond. (Transparent ou blanc par défaut)
   *
   * @param int $r    Couleur. Composante rouge.
   * @param int $g    Couleur. Composante verte.
   * @param int $b    Couleur. Composante bleue.
   */
  public function setBackgroundColor($r, $g, $b)
  {
    $this->bgColor = '#'
      .substr('00' . dechex($red), -2)
      .substr('00' . dechex($green), -2)
      .substr('00' . dechex($blue), -2);
  }

  /**
   * Définit l'opacité de la couleur de remplissage des formes
   *
   * @param int $opacity  Opacité. De 0 à 100.
   * @throws \Exception
   */
  public function setFillColorOpacity($opacity)
  {
    if ($opacity < 0 || $opacity > 100) {
      throw new \Exception('L\'opactité doit être comprise entre 0 et 100');
    }
    $this->fillColorOpacity = $opacity / 100;
  }

  /**
   * Définit la couleur par défaut de remplissage des formes.
   *
   * @param int $r    Couleur. Composante rouge.
   * @param int $g    Couleur. Composante verte.
   * @param int $b    Couleur. Composante bleue.
   */
  public function setFillColor($r, $g, $b)
  {
    $this->fillColor = '#'
      .substr('00' . dechex($red), -2)
      .substr('00' . dechex($green), -2)
      .substr('00' . dechex($blue), -2);
  }

  /**
   * Permet de spécifier qu'un attribut de la forme (properties[*]) sert à déterminer la couleur de remplissage de la forme.
   *
   * @param string $attributeName         Nom de l'attribut à utiliser
   * @param array $correspundanceValues   Si on utilise pas la valeur brute de l'attribut mais qu'on fait appel à un tableau de correspondance du type array('valeur attribut' => '#couleur')
   */
  public function setFillColorOnAttribute($attributeName, $correspundanceValues = null)
  {
    $this->fillColorBasedOnAttribute = $attributeName;
    if ($correspundanceValues) {
      $this->fillColorBasedOnAttributeCorresp = $correspundanceValues;
    }
  }

  /**
   * Permet de définir une fonction de callback à appeler après le traitement
   *
   * @param string $functionName  Nom de la fonction de callback
   * @param string $target        Cible sur laquelle appeler la fonction
   */
  public function setDrawShapeCallBackFunction($functionName, $target = null)
  {
    $this->drawShapeCallBackTarget = $target;
    $this->drawShapeCallBackFunctionName = $functionName;
  }

  /**
   * Modifie l'opacité des contours des formes
   *
   * @param int $opacity  Opacité. De 0 à 100
   * @throws \Exception
   */
  public function setStrokeColorOpacity($opacity)
  {
    if ($opacity < 0 || $opacity > 100) {
      throw new \Exception('L\'opactité doit être comprise entre 0 et 100');
    }
    $this->strokeColorOpacity = $opacity / 100;
  }

  /**
   * Modifie la couleur des tracés des contours des formes.
   *
   * @param int $r    Couleur. Composante rouge.
   * @param int $g    Couleur. Composante verte.
   * @param int $b    Couleur. Composante bleue.
   */
  public function setStrokeColor($r, $g, $b)
  {
    $this->strokeColor = '#'
      .substr('00' . dechex($red), -2)
      .substr('00' . dechex($green), -2)
      .substr('00' . dechex($blue), -2);
  }

  /**
   * Définit la largeur des tracés du contour des formes
   *
   * @param int $width    Largeur
   */
  public function setStrokeWidth($width)
  {
    $this->strokeWidth = $width;
  }

  /**
   * Permet de spécifier qu'un attribut de la forme (properties[*]) sert à déterminer la couleur des contours des formes.
   *
   * @param string $attributeName         Nom de l'attribut à utiliser
   * @param array $correspundanceValues   Si on utilise pas la valeur brute de l'attribut mais qu'on fait appel à un tableau de correspondance du type array('valeur attribut' => '#couleur')
   */
  public function setStrokeColorOnAttribute($attributeName, $correspundanceValues = null)
  {
    $this->strokeColorBasedOnAttribute = $attributeName;
    if ($correspundanceValues) {
      $this->strokeColorBasedOnAttributeCorresp = $correspundanceValues;
    }
  }

  /**
   * Ecrit le résultat dans un fichier.
   *
   * @param string $outputFile    Chemin relatif ou absolu du fichier à écrire
   */
  public function witeInFile($outputFile)
  {
    $this->checkSource();
    $img = $this->getResult();
    $img->writeImage($outputFile);
    $img->destroy();
  }

  /**
   * Ecrit le résultat dans la sortie navigateur
   */
  public function writeInBrowser()
  {
    $this->checkSource();
    $img = $this->getResult();
    header('Content-Type: image/' . $img->getImageFormat());
    print $img;
  }

  /**
   * Retourne le résultat en ressource GD
   *
   * @return ressource
   */
  public function getResultAsGdRessource()
  {
    $this->checkSource();
    $img = $this->getResult();
    $gdi = ImagickGd::convertImagickToGDRessource($img, true);
    return $gdi;
  }

  /**
   * Retourne le résultat en objet Imagick
   *
   * @return \Imagick
   */
  public function getResultAsImagickObject()
  {
    $this->checkSource();
    $img = $this->getResult();
    return $img;
  }

  /**
   * Retourne le nombre de formes tracées
   *
   * @return int
   */
  public function getShapesCount()
  {
    return $this->shapesCount;
  }

  /**
   * Retourne la largeur de l'image de sortie
   *
   * @return int
   */
  public function getOutputWidth()
  {
    return $this->outputWidth;
  }

  /**
   * Retourne la hauteur de l'image de sortie
   *
   * @return int
   */
  public function getOutputHeight()
  {
    return $this->outputheight;
  }

  /**
   * Retourne les coordonnées GPS du point sud ouest extrème.
   *
   * @return array    array('lat' => float, 'lon' => float)
   */
  public function getSOLatLong()
  {
    return $this->geoBoundsLatLong['so'];
  }

  /**
   * Retourne les coordonnées GPS du point nord est extrème.
   *
   * @return array    array('lat' => float, 'lon' => float)
   */
  public function getNELatLong()
  {
    return $this->geoBoundsLatLong['ne'];
  }

  /**
   * Retourne les coordonnées en mètres du point sud ouest extrème.
   *
   * @return array    array('xm' => int, 'ym' => int)
   */
  public function getSOMeters()
  {
    return $this->geoBoundsMeters['so'];
  }

  /**
   * Retourne les coordonnées en mètres du point nord est extrème.
   *
   * @return array    array('xm' => int, 'ym' => int)
   */
  public function getNEMeters()
  {
    return $this->geoBoundsMeters['ne'];
  }

  /**
   * Vérifie qu'une source Json a été transmise
   *
   * @throws \Exception
   */
  private function checkSource()
  {
    if (is_null($this->geoJsonArray)) {
      throw new \Exception('Vous devez spécifier un GeoJson grace aux methodes populateFromGeoJsonFile() ou polulateFromGeoJsonString()');
    }
  }

  /**
   * Vérifie le format Json
   *
   * @param  string $geoJsonString     Chaîne GeoJson
   * @return array
   * @throws \Exception
   */
  private function checkGeoJson($geoJsonString)
  {
    $json = json_decode($data, $geoJsonString);
    if (!is_array($json)) {
      throw new \Exception('Impossible de parcourir le Json, celui-ci ne semble pas valide');
    }
    return $json;
  }

  /**
   * Retourne le résultat du traitement
   *
   * @return \Imagick
   */
  private function getResult()
  {
    $p1m = $this->geoBoundsMeters['so'];
    $p2m = $this->geoBoundsMeters['ne'];

    // Creation image de base
    $im = new \Imagick();
    if ($this->imageFormat == Image::IMAGE_TYPE_JPG) {
      if ($this->bgColor) {
        $im->newImage($this->outputWidth, $this->outputheight, $this->bgColor);
      } else {
        $im->newImage($this->outputWidth, $this->outputheight, '#FFFFFF');
      }
      $im->setCompression(\Imagick::COMPRESSION_JPEG);
      $im->setCompressionQuality($this->imageQuality);
      $im->setImageFormat('jpeg');
    } else if ($this->imageFormat == Image::IMAGE_TYPE_PNG) {
      if ($this->bgColor) {
        $im->newImage($this->outputWidth, $this->outputheight, $this->bgColor);
      } else {
        $im->newImage($this->outputWidth, $this->outputheight, 'none');
      }
      $im->setCompression(\Imagick::COMPRESSION_JPEG);
      $im->setCompressionQuality($this->imageQuality);
      $im->setImageFormat('png');
    }

    // Dessin des formes
    $count = 0;
    foreach ($this->geoJsonArray['features'] AS $forme) {
      $points = array();
      foreach ($forme['geometry']['coordinates'][0][0] AS $coord) {
        $long = $coord[0];
        $lat = $coord[1];
        $pm = $this->latLonToMeters($lat, $long);

        $point = array();
        $point['x'] = $this->getRelX($p1m['xm'], $p2m['xm'], 0, $this->outputWidth, $pm['xm']);
        $point['y'] = $this->getRelY($p1m['ym'], $p2m['ym'], 0, $this->outputheight, $pm['ym']);
        $points[] = $point;
      }

      // Dessin
      $draw = new \ImagickDraw();

      $fillColor = $this->fillColor;
      $fillColorOpacity = $this->fillColorOpacity;
      $strokeWidth = $this->strokeWidth;
      $strokeOpacity = $this->strokeColorOpacity;
      $strokeColor = $this->strokeColor;

      //Couleur de fond
      if ($this->fillColorBasedOnAttribute) {
        if (isset($forme['properties'][$this->fillColorBasedOnAttribute])) {
          if ($this->fillColorBasedOnAttributeCorresp) {
            if (isset($this->fillColorBasedOnAttributeCorresp[$forme['properties'][$this->fillColorBasedOnAttribute]])) {
              $fillColor = $this->fillColorBasedOnAttributeCorresp[$forme['properties'][$this->fillColorBasedOnAttribute]];
            }
          } else {
            $fillColor = $forme['properties'][$this->fillColorBasedOnAttribute];
          }
        }
      }

      //Lignes
      if ($this->strokeWidth > 0) {
        if ($this->strokeColorBasedOnAttribute) {
          if (isset($forme['properties'][$this->strokeColorBasedOnAttribute])) {
            if ($this->strokeColorBasedOnAttributeCorresp) {
              if (isset($this->strokeColorBasedOnAttributeCorresp[$forme['properties'][$this->strokeColorBasedOnAttribute]])) {
                $strokeColor = $this->strokeColorBasedOnAttributeCorresp[$forme['properties'][$this->strokeColorBasedOnAttribute]];
              }
            } else {
              $strokeColor = $forme['properties'][$this->strokeColorBasedOnAttribute];
            }
          }
        }
      }

      if ($this->drawShapeCallBackFunctionName) {
        $data = array(
          'fillColor' => $fillColor,
          'fillOpacity' => $fillColorOpacity,
          'strokeWidth' => $strokeWidth,
          'strokeColor' => $strokeColor,
          'strokeOpacity' => $strokeOpacity
        );

        $ret = $data;
        if ($this->drawShapeCallBackTarget) {
          $ret = call_user_func(array($this->drawShapeCallBackTarget, $this->drawShapeCallBackFunctionName), $data);
        } else {
          $ret = call_user_func($this->drawShapeCallBackFunctionName, $data);
        }

        foreach ($data AS $k => $v) {
          if(isset($ret[$k])){
            $data[$k] = $ret[$k];
          }
        }

        $fillColor = $data['fillColor'];
        $fillColorOpacity = $data['fillOpacity'];
        $strokeColor = $data['strokeColor'];
        $strokeOpacity = $data['strokeOpacity'];
        $strokeWidth = $data['strokeWidth'];
      }

      if($strokeWidth > 0){
        $draw->setStrokeColor($strokeColor);
        $draw->setstrokealpha($strokeOpacity);
        $draw->setstrokewidth($strokeWidth);
      }

      $draw->setFillColor($fillColor);
      $draw->setfillopacity($fillColorOpacity);

      $draw->polygon($points);
      $im->drawImage($draw);

      $count++;
    }

    $im->settype(6);
    return $im;
  }

  /**
   * Analyse du GeoJson
   */
  private function analyse()
  {
    $this->calculateGeoBound();
    $this->calculateOutputSize();
  }

  /**
   * Calcul des coordonnées GPS extrèmes
   */
  private function calculateGeoBound()
  {
    $minLat = 1000;
    $maxLat = 0;
    $minLong = 1000;
    $maxLong = 0;

    $nbFormes = 0;
    foreach ($this->geoJsonArray['features'] AS $forme) {
      $nbFormes++;
      foreach ($forme['geometry']['coordinates'][0][0] AS $coord) {
        $long = $coord[0];
        $lat = $coord[1];
        if ($long < $minLong) {
          $minLong = $long;
        }
        if ($long > $maxLong) {
          $maxLong = $long;
        }
        if ($lat < $minLat) {
          $minLat = $lat;
        }
        if ($lat > $maxLat) {
          $maxLat = $lat;
        }
      }
    }

    $this->shapesCount = $nbFormes;

    $p1m = $this->latLonToMeters($minLat, $minLong);
    $p2m = $this->latLonToMeters($maxLat, $maxLong);

    $this->geoBoundsMeters = array('so' => $p1m, 'ne' => $p2m);
    $this->geoBoundsLatLong = array(
      'so' => array('lat' => $minLat, 'lon' => $minLong),
      'ne' => array('lat' => $maxLat, 'lon' => $maxLong)
    );
  }

  /**
   * Calcul de la taille de l'image de sortie
   */
  private function calculateOutputSize()
  {
    $p1m = $this->geoBoundsMeters['so'];
    $p2m = $this->geoBoundsMeters['ne'];

    $outputWidth = 0;
    $outputHeight = 0;
    $diffXm = $p2m['xm'] - $p1m['xm'];
    $diffYm = $p2m['ym'] - $p1m['ym'];
    if ($diffYm > $diffXm) {
      // La longitude (X) sera à taille minimale. La latitude sera supérieure.
      $outputWidth = $this->minSize;
      $outputHeight = round($this->minSize * $diffYm / $diffXm);
    } else if ($diffXm > $diffYm) {
      // La latitude (Y) sera à taille minimale. La longitude sera supérieure.
      $outputHeight = $this->minSize;
      $outputWidth = round($this->minSize * $diffXm / $diffYm);
    } else {
      // Longitude et latitude seront à tailles égales. Width = height
      $outputHeight = $this->minSize;
      $outputWidth = $this->minSize;
    }

    $this->outputWidth = $outputWidth;
    $this->outputheight = $outputHeight;
  }

  /**
   * Calcul d'une coordonnée relative x en fonction des coordonnées min / max en mètres
   *
   * @param  int $minXm    Coordonnée min X en mètres
   * @param  int $maxXm    Coordonnée max X en mètres
   * @param  int $minX     X minimum (0 généralement)
   * @param  int $maxX     X minimum (largeur de l'image généralement)
   * @param  int $xm       Coordonnée X en mètre à transformer en relatif
   * @return int
   */
  private function getRelX($minXm, $maxXm, $minX, $maxX, $xm)
  {
    return round($minX + (($maxX - $minX) * (($xm - $minXm) / ($maxXm - $minXm))));
  }

  /**
   * Calcul d'une coordonnée relative y en fonction des coordonnées min / max en mètres
   *
   * @param  int $minYm    Coordonnée min Y en mètres
   * @param  int $maxYm    Coordonnée max Y en mètres
   * @param  int $minY     Y minimum (0 généralement)
   * @param  int $maxY     Y minimum (hauteur de l'image généralement)
   * @param  int $ym       Coordonnée Y en mètre à transformer en relatif
   * @return int
   */
  private function getRelY($minYm, $maxYm, $minY, $maxY, $ym)
  {
    return round($maxY - (($maxY - $minY) * (($ym - $minYm) / ($maxYm - $minYm))));
  }

  /**
   * Converts given lat/lon in WGS84 Datum to XY in Spherical Mercator EPSG:900913"
   * Duplicate from Jin2\Geo\Google\Maps\GeoProjectionMercator::latLonToMeters()
   *
   * @param  float $lat
   * @param  float $lon
   * @return array array('xm' => float, 'xy' => float)
   */
  private function latLonToMeters($lat, $lon)
  {
    $mx = $lon * $this->originShift / 180.0;
    $my = log(tan((90 + $lat) * M_PI / 360.0)) / (M_PI / 180.0);
    $my = $my * $this->originShift / 180.0;
    return array('xm' => $mx, 'ym' => $my);
  }

}
