<?php

namespace frontend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Sales;

/**
 * SalesSearch represents the model behind the search form of `backend\models\Sales`.
 */
class SalesSearch extends Sales
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'session_id', 'pump_no', 'nozzel_no', 'pts_transaction_no', 'status'], 'integer'],
            [['trn_dt', 'product', 'currency', 'qr_code','date1','date2'], 'safe'],
            [['volume', 'price', 'sub_total', 'total'], 'number'],
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
        $query = Sales::find();

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

            'session_id' => $this->session_id,
            'volume' => $this->volume,
            'price' => $this->price,
            'sub_total' => $this->sub_total,
            'total' => $this->total,
            'pump_no' => $this->pump_no,
            'nozzel_no' => $this->nozzel_no,
            'pts_transaction_no' => $this->pts_transaction_no,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'product', $this->product])
            ->andFilterWhere(['like', 'currency', $this->currency])
            ->andFilterWhere(['between', 'trn_dt', $this->date1,$this->date2])
            ->andFilterWhere(['like', 'qr_code', $this->qr_code]);

        return $dataProvider;
    }
}
