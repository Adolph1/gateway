<?php

namespace backend\modules\webvfd\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\modules\webvfd\models\WebVfdApi;

/**
 * WebVfdApiSearch represents the model behind the search form of `backend\modules\webvfd\models\WebVfdApi`.
 */
class WebVfdApiSearch extends WebVfdApi
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['request_title', 'request_name', 'url', 'maker', 'maker_time'], 'safe'],
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
        $query = WebVfdApi::find();

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
            'maker_time' => $this->maker_time,
        ]);

        $query->andFilterWhere(['like', 'request_title', $this->request_title])
            ->andFilterWhere(['like', 'request_name', $this->request_name])
            ->andFilterWhere(['like', 'url', $this->url])
            ->andFilterWhere(['like', 'maker', $this->maker]);

        return $dataProvider;
    }
}
