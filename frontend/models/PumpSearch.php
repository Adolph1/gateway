<?php

namespace frontend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Pump;

/**
 * PumpSearch represents the model behind the search form of `backend\models\Pump`.
 */
class PumpSearch extends Pump
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'pump_number', 'pump_address'], 'integer'],
            [['name', 'maker', 'maker_time'], 'safe'],
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
        $query = Pump::find();

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
            'pump_number' => $this->pump_number,
            'pump_address' => $this->pump_address,
            'maker_time' => $this->maker_time,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'maker', $this->maker]);

        return $dataProvider;
    }
}
