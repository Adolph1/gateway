<?php

namespace frontend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\PumpData;

/**
 * PumpDataSearch represents the model behind the search form of `backend\models\PumpData`.
 */
class PumpDataSearch extends PumpData
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'transaction', 'status'], 'integer'],
            [['pump_data', 'transID', 'date_time'], 'safe'],
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
        $query = PumpData::find();

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
            'transaction' => $this->transaction,
            'status' => $this->status,
            'date_time' => $this->date_time,
        ]);

        $query->andFilterWhere(['like', 'pump_data', $this->pump_data])
            ->andFilterWhere(['like', 'transID', $this->transID]);

        return $dataProvider;
    }
}
