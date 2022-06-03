<?php

namespace Drupal\calamari_config\Plugin\Block;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'SearchPathBlock' block.
 *
 * @Block(
 *  id = "search_path_block",
 *  admin_label = @Translation("Search path block"),
 * )
 */
class SearchPathBlock extends BlockBase {

    /**
     * {@inheritdoc}
     */
    public function build() {

        $search_api_fulltext = \Drupal::request()->query->get('key');
        $field_doc_pmencionadas = \Drupal::request()->query->get('field_doc_pmencionadas');
        $field_doc_cargo1 = \Drupal::request()->query->get('field_doc_cargo1');
        $field_doc_institucion = \Drupal::request()->query->get('field_doc_institucion');
        $field_ref_tematicos = \Drupal::request()->query->get('field_ref_tematicos');
        $field_ref_geograficos = \Drupal::request()->query->get('field_ref_geograficos');
        $all = \Drupal::request()->query->all();

        if (!empty($search_api_fulltext)) {
            $data["Palabras Clave"] = $search_api_fulltext;
        }

        if (!empty($field_doc_pmencionadas) && $field_doc_pmencionadas != 'All') {
            $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($field_doc_pmencionadas);
            $data["Personas Mencionadas"] = $term->label();
        }

        if (!empty($field_doc_cargo1) && $field_doc_cargo1 != 'All') {
            $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($field_doc_cargo1);
            $data["Cargo"] = $term->label();
        }

        if (!empty($field_doc_institucion) && $field_doc_institucion != 'All') {
            $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($field_doc_institucion);
            $data["Institución"] = $term->label();
        }

        if (!empty($field_ref_tematicos) && $field_ref_tematicos != 'All') {
            $data["Temáticos"] = $this->getParentsTerm($field_ref_tematicos);
        }

        if (!empty($field_ref_geograficos) && $field_ref_geograficos != 'All') {
            $data["Geográficos"] = $this->getParentsTerm($field_ref_geograficos);
        }
        if (array_key_exists('f', $all)) {
            $$txtadicional = "";
            foreach ($all['f'] as $clave => $valor) {
                $filtro = explode(":", $valor);
                $termino = $this->getParentsTerm($filtro[1]);
                $txtadicional .= ucfirst(str_replace("_", " ", $filtro[0])) . ' : ' . $termino . ' - ';
            }
           $txtadicional = substr($txtadicional, 0, -2);
            $data["Refinado por"] =  $txtadicional;
        }

        $build['infoSearch'] = [
            '#theme' => 'infosearch',
            '#data' => $data,
            '#cache' => ['max-age' => 0]
        ];

        return $build;
    }

    protected function getParentsTerm($tid) {
        $loadParents = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadAllParents($tid);
        $parents = array_reverse(array_keys($loadParents));
        $str = "";
        foreach ($parents as $k => $p) {
            $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($p);
            $str .= $term->label();
            if ($k < (count($parents) - 1)) {
                $str .= " > ";
            }
        }
        return $str;
    }

}
