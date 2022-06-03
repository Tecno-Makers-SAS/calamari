<?php
namespace Drupal\importexcel\Form;

use Drupal;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\paragraphs\Entity\Paragraph;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\file\FileUsage\DatabaseFileUsageBackend;
use Drupal\Core\Session\AccountProxyInterface;
use PhpOffice\PhpSpreadsheet\IOFactory as IOFactoryAlias;
use Drupal\node\Entity\Node;

/**
 * Class ImportExcelForm.
 */
class ImportExcelForm extends FormBase {


  /**
   * Container Service File and user
   */
  private $fileUsage;
  private $current_user;

  public function __construct(DatabaseFileUsageBackend $fileUsage, AccountProxyInterface $current_user) {
    $this->fileUsage = $fileUsage;
    $this->current_user = $current_user;
  }

  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('file.usage'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'import_excel_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm( array $form, FormStateInterface $form_state ) {

    $form['archivo'] = [
      '#type' => 'managed_file',
      '#upload_location' => 'public://import_excel',
      '#title' => $this->t('Archivo'),
      '#upload_validators' => [
        'file_validate_extensions' => ['xls xlsx'],
      ],
      '#weight' => '0',
    ];

    $form['totalfilas'] = [
      '#type' => 'number',
      '#title' => $this->t('No Fila Final'),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Importar'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @TODO: Validate fields.
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function submitForm( array &$form, FormStateInterface $form_state ) {
    $image = $form_state->getValue('archivo');
    $totalrows = $form_state->getValue('totalfilas');

    $file = File::load( $image[0] );
    $file->save();
    $absolute_path = Drupal::service('file_system')->realpath($file->get('uri')->value);
    Drupal::messenger()->addMessage($this->t('File uploaded: '. $file->get('filename')->value ).' => '.$absolute_path );
    $sheetVector = $this->loadFile($absolute_path);
    $this->processFile($sheetVector, $totalrows);

  }

  protected function loadFile( $pathFile ) {
    $inputFileType = IOFactoryAlias::identify($pathFile);
    $reader = IOFactoryAlias::createReader($inputFileType);
    $spreadsheet = $reader->load($pathFile);
    $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
    return $sheetData;
  }

  protected function processFile( $dataVector, $totalrows ) {
    //Load Node
    $vectorSlice = array_slice($dataVector, 3, $totalrows);
    $pageVector =  array_chunk($vectorSlice, 10);
    $operations = [];
    foreach ($pageVector as $small_vector){
      $operations[] = ['\Drupal\importexcel\Form\ImportExcelForm::batchProcessFile', [$small_vector]];
    }

    // Setup and define batch informations.
    $batch = array(
      'title' => t('Importando Registros Archivo Excel in batch...'),
      'operations' => $operations,
      'finished' => '\Drupal\importexcel\Form\ImportExcelForm::batchFinished',
    );

    batch_set($batch);
  }

  public static function batchProcessFile($dataVector, &$context) {

    foreach ( $dataVector as $row ) {
       $nid = self::getNidByField( 'title', $row['B'] );
        if( !empty($nid) ) {
          self::updateDoc( $nid[0], $row);
        } else {
          self::createDoc($row);
        }
    }

    //info proccess batch
    $batch_size=sizeof($dataVector);
    $batch_number=sizeof($context['results'])+1;
    $context['message'] = sprintf("Importando %s nodes por Batch. Batch #%s", $batch_size, $batch_number);
    $context['results'][] = sizeof($dataVector);
  }

  public static function batchFinished($success, $results) {
    if ($success)
      $message = count($results). ' Registros procesados.';
    else
      $message = 'Importación Generada con error.';
    Drupal::messenger()->addMessage($message);
  }

  public static function updateDoc( $nid, $objectNode ) {
    try {
      $node = Node::load($nid);
      if (!empty(trim($objectNode['A']))) {
        $node->get('field_doc_topologia')->target_id = self::getTidByName(trim($objectNode['A']), 'topologia');
      }
      if (!empty(trim($objectNode['B']))) {
        $node->get('title')->value = trim($objectNode['B']);
      }
      if (!empty(trim($objectNode['C']))) {
        $node->get('field_doc_fagi')->target_id = self::getTidByName(trim($objectNode['C']), 'fagi');
      }
      if (!empty(trim($objectNode['D']))) {
        $node->get('field_doc_nlegajo')->value = trim($objectNode['D']);
      }
      if (!empty(trim($objectNode['E']))) {
        $node->get('field_doc_nolibro')->value = trim($objectNode['E']);
      }
      if (!empty(trim($objectNode['F']))) {
        $node->get('field_doc_nodoc')->value = trim($objectNode['F']);
      }
      if (!empty(trim($objectNode['G']))) {
        $node->get('field_doc_nfolio')->value = trim($objectNode['G']);
      }
      if (!empty(trim($objectNode['H']))) {
        $node->get('field_doc_nimgdigital')->value = trim($objectNode['H']);
      }
      if (!empty(trim($objectNode['I']))) {
        $node->get('field_doc_cantimg')->value = trim($objectNode['I']);
      }
      if (!empty(trim($objectNode['J']))) {
        $node->get('field_doc_topagi')->target_id = self::getTidByName(trim($objectNode['J']), 'tdagi');
      }
      if (!empty(trim($objectNode['K']))) {
        $node->get('field_doc_clasedoc')->target_id = self::getTidByName(trim($objectNode['K']), 'cdocumento');
      }
      if (!empty(trim($objectNode['L']))) {
        $node->get('field_doc_procedencia')->target_id = self::getTidByName(trim($objectNode['L']), 'procedencia');
      }
      if (!empty(trim($objectNode['M']))) {
        $node->get('field_doc_remitente')->target_id = self::getTidByName(trim($objectNode['M']), 'remitente');
      }
      if (!empty(trim($objectNode['N']))) {
        $node->get('field_doc_destinatario')->target_id = self::getTidByName(trim($objectNode['N']), 'destinatario');
      }
      if (!empty(trim($objectNode['O']))) {
        $node->get('field_doc_flimite')->value = trim($objectNode['O']);
      }
      if (!empty(trim($objectNode['P']))) {
        $node->get('field_doc_lexpedicion')->target_id = self::getTidByName(trim($objectNode['P']), 'lugar_de_expedicion');
      }
      if (!empty(trim($objectNode['Q']))) {
        $date = self::formatDateFile(trim($objectNode['Q']), trim($objectNode['R']), trim($objectNode['S']));
        $node->get('field_doc_fexpedicion')->value = $date;
      }
      if (!empty(trim($objectNode['T']))) {
        $node->get('field_doc_title')->value = $objectNode['T'];
      }
      if (!empty($objectNode['U'])) {
        $node->get('field_doc_body1')->value = $objectNode['U'];
      }
      if (!empty($objectNode['V'])) {
        $node->get('field_doc_body2')->value = $objectNode['V'];
      }
      //Onomastico
      $onomastico = self::createOno(trim($objectNode['W']), trim($objectNode['X']), trim($objectNode['Y']));
      
      if (is_array($onomastico)) {
        $node->field_ref_onomasticos[] = [
          'target_id' => $onomastico['id'],
          'target_revision_id' => $onomastico['rid']
        ];       
       
      }

      //tematicos
      $node->field_ref_tematicos[] = self::getTematico(trim($objectNode['Z']), trim($objectNode['AA']), trim($objectNode['AB']));

      //Institucion
      if (!empty(trim($objectNode['AC']))) {
        $node->field_doc_institucion[] = ['target_id' => self::getTidByName(trim($objectNode['AC']), 'instituciones')];
      }

      //Geograficos
      $idubica = self::getGeo(trim($objectNode['AD']), trim($objectNode['AE']), trim($objectNode['AF']), trim($objectNode['AG']));
      $geografico = self::createGeo($idubica, trim($objectNode['AH']), trim($objectNode['AI']), trim($objectNode['AJ']));
      if (is_array($geografico)) {
        $node->field_doc_geograficos[] = [
          'target_id' => $geografico['id'],
          'target_revision_id' => $geografico['rid'],
        ];
      } 
       $node->save();
    
    }catch (Exception $e){
      Drupal::logger('importexcel')->error($e->getMessage()."- Update Node: ".$objectNode['B']);
    }

  }

  public static function createDoc( $objectNode ) {
    try {
      $doc = Node::create(['type' => 'documento', 'uid' => 1, 'status' => 1]);
      if (!empty(trim($objectNode['A']))) {
        $doc->set('field_doc_topologia', ['target_id' => self::getTidByName(trim($objectNode['A']), 'topologia')]);
      }
      $doc->set('title', trim($objectNode['B']));
      if (!empty(trim($objectNode['C']))) {
        $doc->set('field_doc_fagi', ['target_id' => self::getTidByName(trim($objectNode['C']), 'fagi')]);
      }
      $doc->set('field_doc_nlegajo', trim($objectNode['D']));
      $doc->set('field_doc_nolibro', trim($objectNode['E']));
      $doc->set('field_doc_nodoc', trim($objectNode['F']));
      $doc->set('field_doc_nfolio', trim($objectNode['G']));
      $doc->set('field_doc_nimgdigital', trim($objectNode['H']));
      $doc->set('field_doc_cantimg', trim($objectNode['I']));
      if (!empty(trim($objectNode['J']))) {
        $doc->set('field_doc_topagi', ['target_id' => self::getTidByName(trim($objectNode['J']), 'tdagi')]);
      }
      if (!empty(trim($objectNode['K']))) {
        $doc->set('field_doc_clasedoc', ['target_id' => self::getTidByName(trim($objectNode['K']), 'cdocumento')]);
      }
      if (!empty(trim($objectNode['L']))) {
        $doc->set('field_doc_procedencia', ['target_id' => self::getTidByName(trim($objectNode['L']), 'procedencia')]);
      }
      if (!empty(trim($objectNode['M']))) {
        $doc->set('field_doc_remitente', ['target_id' => self::getTidByName(trim($objectNode['M']), 'remitente')]);
      }
      if (!empty(trim($objectNode['N']))) {
        $doc->set('field_doc_destinatario', ['target_id' => self::getTidByName(trim($objectNode['N']), 'destinatario')]);
      }
      $doc->set('field_doc_flimite', trim($objectNode['O']));
      if (!empty(trim($objectNode['P']))) {
        $doc->set('field_doc_lexpedicion', ['target_id' => self::getTidByName(trim($objectNode['P']), 'lugar_de_expedicion')]);
      }
      $date = self::formatDateFile(trim($objectNode['Q']), trim($objectNode['R']), trim($objectNode['S']));
      $doc->set('field_doc_fexpedicion', $date);
      $doc->set('field_doc_title', $objectNode['T']);
      $doc->set('field_doc_body1', $objectNode['U']);
      $doc->set('field_doc_body2', $objectNode['V']);
      //onomastico
      $onomastico = self::createOno(trim($objectNode['W']), trim($objectNode['X']), trim($objectNode['Y']));
      if (is_array($onomastico)) {
        $arOno[] = [
          'target_id' => $onomastico['id'],
          'target_revision_id' => $onomastico['rid'],
        ];
        $doc->set('field_ref_onomasticos', $arOno);
      }

      //tematicos
      $arTerm[] = self::getTematico(trim($objectNode['Z']), trim($objectNode['AA']), trim($objectNode['AB']));
      $doc->set('field_ref_tematicos', $arTerm);

      //Institucion
      if (!empty(trim($objectNode['AC']))) {
        $inst[] = ['target_id' => self::getTidByName(trim($objectNode['AC']), 'instituciones')];
        $doc->set('field_doc_institucion', $inst);
      }

      //Geograficos
      $idubica = self::getGeo(trim($objectNode['AD']), trim($objectNode['AE']), trim($objectNode['AF']), trim($objectNode['AG']));
      $geografico = self::createGeo($idubica, trim($objectNode['AH']), trim($objectNode['AI']), trim($objectNode['AJ']));
      if (is_array($geografico)) {
        $arGeo[] = [
          'target_id' => $geografico['id'],
          'target_revision_id' => $geografico['rid'],
        ];
        $doc->set('field_doc_geograficos', $arGeo);
      }

      $doc->enforceIsNew();   
      $doc->save();    
    } catch ( Exception $e){
      Drupal::logger('importexcel')->error($e->getMessage()."- Insert Node: ".$objectNode['B']);
    }
  }

  public static function getNidByField( $fieldSearch, $valueSearch ) {
    $query = Drupal::entityQuery('node')
      ->condition('type','documento')
      ->condition($fieldSearch, $valueSearch);
    return array_values($query->execute());
  }

  public static function getTidByName($name = NULL, $vid = NULL) {
    $properties = [];
    if (!empty($name)) {
      $properties['name'] = $name;
    }
    if (!empty($vid)) {
      $properties['vid'] = $vid;
    }
    $terms = Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties);
    $term = reset($terms);
    return !empty($term) ? $term->id() : 0;
  }

  public static function formatDateFile( $yy, $mm, $dd ) {

      if( !empty($dd) ) {
        $day = $dd;
      } else {
        $day = '01';
      }
      //mount
      if( !empty($mm) ) {
        $mount = $mm;
      } else {
        $mount = '01';
      }
      //year
      if( !empty($yy ) ) {
        $year = $yy;
      } else {
        $year = '0001';
      }
      $d = new DrupalDateTime($year.'-'.$mount.'-'.$day);
      return $d->format('Y-m-d');
  }

  // Create Onomastico
  public static function createOno( $person, $job1, $job2 ) {

      if(empty($person)){
        $person = '_none';
      } else {
        $person = ['target_id' => self::getTidByName($person, 'personas_mencionadas')];
      }

      if(empty($job1)){
        $job1 = '_none';
      } else {
        $job1 = ['target_id' => self::getTidByName($job1, 'cargos')];
      }

      if(empty($job2)){
        $job2 = '_none';
      } else {
        $job2 = ['target_id' => self::getTidByName($job2, 'cargos')];
      }

      if( ( $person == '_none' ) && ( $job1 == '_none' ) && ($job2 == '_none') ) {
        $ar_parag = false;
      } else {
        $paragraph = Paragraph::create([
          'type' => 'onomasticos',
          'field_doc_pmencionadas' => $person,
          'field_doc_cargo1' => $job1,
          'field_doc_cargo2' => $job2
        ]);
        $paragraph->save();
        $ar_parag['id'] = $paragraph->id();
        $ar_parag['rid'] = $paragraph->getRevisionId();
      }
      return $ar_parag;
  }


  //Crear Geografico
  public static function createGeo( $ubicacion, $lugar, $region, $pueblo ) {

    if(empty($ubicacion) || $ubicacion == 0 ){
      $ubicacion = '_none';
    } else {
      $ubicacion = ['target_id' => $ubicacion ];
    }

    if(empty($lugar)){
      $lugar = '_none';
    } else {
      $lugar = ['target_id' => self::getTidByName($lugar, 'nivel_5_lugar_especifico')];
    }

    if(empty($region)){
      $region = '_none';
    } else {
      $region = ['target_id' => self::getTidByName($region, 'doc_original_region')];
    }

    if(empty($pueblo)){
      $pueblo = '_none';
    } else {
      $pueblo = ['target_id' => self::getTidByName($pueblo, 'doc_original_pueblo')];
    }

    if( ( $ubicacion == '_none' ) && ( $lugar == '_none' ) && ($pueblo == '_none') && ($region == '_none') ) {
      $ar_parag = false;
    } else {
      $paragraph = Paragraph::create([
        'type' => 'geograficos',
        'field_ref_geograficos' => $ubicacion,
        'field_ref_n5lugar' => $lugar,
        'field_ref_docpueblo' => $pueblo,
        'field_ref_docregion' => $region
      ]);

      $paragraph->save();
      $ar_parag['id'] = $paragraph->id();
      $ar_parag['rid'] = $paragraph->getRevisionId();
    }
    return $ar_parag;
  }


  public static function getGeo( $n1, $n2, $n3, $n4 ){
    $tid = [];
    if(!empty( $n4 )) {
      $tid = ['target_id' => self::getTidByName($n4, 'geograficos' )];
      //$n4 = $tid;
    } else {
      if(!empty( $n3 )) {
        $tid = ['target_id' => self::getTidByName($n3, 'geograficos' )];
        //$n3 = $tid;
      } else {
        if(!empty( $n2 )){
          $tid = ['target_id' => self::getTidByName($n2, 'geograficos' )];
          //$n2 = $tid;
        } else {
          if(!empty( $n1 )){
            $tid = ['target_id' => self::getTidByName($n1, 'geograficos' )];
            //$n1 = $tid;
          }
        }
      }
    }
    return $tid['target_id'];
  }

  //Traer Tid Tematico
  public static  function getTematico( $t1, $t2, $t3 ) {
    if( !empty( $t1 ) ) {
      $tid = self::getTidByNameParent($t1, 'tematicos');
      if( !empty( $t2 ) && !empty($tid) ){
        $ar_padre = self::getChildrenTerms($t2, $tid);
        $tid = reset($ar_padre);
        if( !empty( $t3 ) && !empty($tid) ){
          $ar_son = self::getChildrenTerms($t3, $tid);
          $tid = reset($ar_son);
        }
      }
      return $tid;
    }
    /*else {
      return '_none';
    }*/
  }

  public static function getTidByNameParent($name = NULL, $vid = NULL) {
    $properties = [];
    if (!empty($name)) {
      $properties['name'] = $name;
    }
    if (!empty($vid)) {
      $properties['vid'] = $vid;
    }

    $terms = Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadByProperties($properties);

    return ImportExcelForm::compareParent(array_keys($terms));
  }

  public static function compareParent( $tids ) {
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadMultiple($tids);
    foreach ($terms as $term) {
      if ( $term->parent->target_id == 0 ) {
        $tid = $term->id();
      }
    }
    return $tid;
  }

  public static function getChildrenTerms($name, $parent_id ) {
    $query = Drupal::entityQuery('taxonomy_term')
      ->condition('parent', $parent_id)
      ->condition('name', $name);
    return $query->execute();
  }

  public static function debug( $var ) {
    echo '<br/><br/><br/><pre>';
    print_r( $var );
    echo '</pre> <hr/>';
  }

}
