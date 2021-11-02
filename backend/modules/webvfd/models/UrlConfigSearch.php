<?php

namespace backend\modules\webvfd\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\modules\webvfd\models\UrlConfig;

/**
 * UrlConfigSearch represents the model behind the search form of `backend\models\UrlConfig`.
 */
class UrlConfigSearch extends UrlConfig
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_by'], 'integer'],
            [['name', 'prefix_url', 'post_fix', 'domain_name', 'url', 'created_at'], 'safe'],
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
        $query = UrlConfig::find();

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
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'prefix_url', $this->prefix_url])
            ->andFilterWhere(['like', 'post_fix', $this->post_fix])
            ->andFilterWhere(['like', 'domain_name', $this->domain_name])
            ->andFilterWhere(['like', 'url', $this->url]);

        return $dataProvider;
    }
}
