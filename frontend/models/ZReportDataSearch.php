<?php

namespace frontend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\ZReportData;

/**
 * ZReportDataSearch represents the model behind the search form of `frontend\models\ZReportData`.
 */
class ZReportDataSearch extends ZReportData
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'company_id', 'znumber', 'status'], 'integer'],
            [['fiscal_code', 'vatrate', 'pmttype', 'datetime', 'ackmsg','date1','date2'], 'safe'],
            [['nettamount', 'discount', 'taxamount', 'pmtamount'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = ZReportData::find();


        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'company_id' => $this->company_id,
            'znumber' => $this->znumber,
            'nettamount' => $this->nettamount,
            'discount' => $this->discount,
            'taxamount' => $this->taxamount,
            'pmtamount' => $this->pmtamount,

            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'fiscal_code', $this->fiscal_code])
            ->andFilterWhere(['like', 'vatrate', $this->vatrate])
            ->andFilterWhere(['like', 'pmttype', $this->pmttype])
            ->andFilterWhere(['between', 'datetime', $this->date1, $this->date2])
            ->andFilterWhere(['like', 'ackmsg', $this->ackmsg]);
        $query->groupBy(['znumber']);

        return $dataProvider;
    }
}
