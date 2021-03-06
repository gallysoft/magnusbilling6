<?php
/**
 * Acoes do modulo "CallShopCdr".
 *
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2016 MagnusBilling. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnusbilling/mbilling/issues
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com>
 * 19/09/2012
 */

class CallShopCdrController extends Controller
{
    public $attributeOrder = 't.date DESC';
    public $select         = 't.id, t.sessionid, t.id_user, t.id_prefix, t.status, buycost, price, calledstation,
                    t.date, sessiontime, cabina, (((t.price - t.buycost) / t.buycost) * 100) markup';
    public $extraValues = array('idUser' => 'username', 'idPrefix' => 'destination');
    public $config;
    public $fieldsFkReport = array(
        'id_user'   => array(
            'table'       => 'pkg_user',
            'pk'          => 'id',
            'fieldReport' => 'username',
        ),
        'id_prefix' => array(
            'table'       => 'pkg_prefix',
            'pk'          => 'id',
            'fieldReport' => 'destination',
        ),
    );

    public function init()
    {
        if (!Yii::app()->session['id_user']) {
            exit;
        }

        $this->instanceModel = new CallShopCdr;
        $this->abstractModel = CallShopCdr::model();
        $this->titleReport   = Yii::t('yii', 'CallShop');
        parent::init();
    }

    public function applyFilterToLimitedAdmi2()
    {
        if (Yii::app()->session['user_type'] == 1 && Yii::app()->session['adminLimitUsers'] == true) {
            $this->relationFilter = array(
                'idUser' => array(
                    'condition' => "id_group IN (SELECT gug.id_group FROM pkg_group_user_group gug WHERE gug.id_group_user = :idgA0) ",
                ),
            );
            $this->paramsFilter['idgA0'] = Yii::app()->session['id_group'];
        }
    }

    public function repaceColumns($columns)
    {
        for ($i = 0; $i < count($columns); $i++) {

            if ($columns[$i]['dataIndex'] == 'idUserusername') {
                $columns[$i]['dataIndex'] = 'id_user';
            } else if ($columns[$i]['dataIndex'] == 'idPrefixdestination') {
                $columns[$i]['dataIndex'] = 'id_prefix';
            } else if ($columns[$i]['dataIndex'] == 'idPrefixprefix') {
                $columns[$i]['dataIndex'] = 'id_prefix';
            } else if ($columns[$i]['dataIndex'] == 'idPhonebookt.name') {
                $columns[$i]['dataIndex'] = 'id_phonebook';
            } else if ($columns[$i]['dataIndex'] == 'idDiddid') {
                $columns[$i]['dataIndex'] = 'id_did';
            }

        }
        return $columns;
    }

    public function getSumPrice()
    {
        return $this->abstractModel->find(array(
            'select'    => "SUM(price) price",
            'join'      => $this->join,
            'condition' => $this->filter,
            'params'    => $this->paramsFilter,
        )
        );
    }

    public function beforeReport($columns)
    {
        //gerar total a pagar no pdf

        $modelCallShop = $this->getSumPrice();

        $this->titleReport    = $this->config['global']['base_currency'] . ' ' . round($modelCallShop->price, 2);
        $this->subTitleReport = Yii::t('yii', 'priceSun');

        $this->join          = '';
        $this->defaultFilter = 1;

        return $columns;
    }

    public function setAttributesModels($attributes, $models)
    {
        $modelCallShop = $this->getSumPrice();

        for ($i = 0; $i < count($attributes) && is_array($attributes); $i++) {

            $attributes[$i]['priceSum'] = round($modelCallShop->price, 2);

        }

        return $attributes;
    }
}
