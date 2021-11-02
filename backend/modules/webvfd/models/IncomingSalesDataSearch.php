<?php

namespace backend\modules\webvfd\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\modules\webvfd\models\IncomingSalesData;

/**
 * IncomingSalesDataSearch represents the model behind the search form of `backend\modules\webvfd\models\IncomingSalesData`.
 */
class IncomingSalesDataSearch extends IncomingSalesData
{


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'company_id', 'status'], 'integer'],
            [['sales_data','qrCode', 'date_time'], 'safe'],
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
        $query = IncomingSalesData::find();

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
            'status' => $this->status,
            'company_id' => $this->company_id,
            'date_time' => $this->date_time,
        ]);

        $query->andFilterWhere(['like', 'sales_data', $this->sales_data])
            ->andFilterWhere(['like', 'qrCode', $this->qrCode]);

        return $dataProvider;
    }
}
