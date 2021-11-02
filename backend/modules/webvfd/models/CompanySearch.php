<?php

namespace backend\modules\webvfd\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\modules\webvfd\models\Company;

/**
 * CompanySearch represents the model behind the search form of `backend\modules\webvfd\models\Company`.
 */
class CompanySearch extends Company
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'tin', 'company_id_type', 'status', 'reg_status', 'approved_by'], 'integer'],
            [['name', 'certificate_password', 'certificate_serial', 'vrn', 'serial_number', 'receipt_number', 'receipt_v_number', 'registration_id', 'uin', 'tax_office', 'address', 'street', 'city', 'country', 'email', 'business_licence', 'contact_person', 'file', 'company_username', 'password', 'approved_at', 'create_by', 'created_at', 'updated_at'], 'safe'],
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
        $query = Company::find();

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
            'tin' => $this->tin,
            'company_id_type' => $this->company_id_type,
            'status' => $this->status,
            'reg_status' => $this->reg_status,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'certificate_password', $this->certificate_password])
            ->andFilterWhere(['like', 'certificate_serial', $this->certificate_serial])
            ->andFilterWhere(['like', 'vrn', $this->vrn])
            ->andFilterWhere(['like', 'serial_number', $this->serial_number])
            ->andFilterWhere(['like', 'receipt_number', $this->receipt_number])
            ->andFilterWhere(['like', 'receipt_v_number', $this->receipt_v_number])
            ->andFilterWhere(['like', 'registration_id', $this->registration_id])
            ->andFilterWhere(['like', 'uin', $this->uin])
            ->andFilterWhere(['like', 'tax_office', $this->tax_office])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'street', $this->street])
            ->andFilterWhere(['like', 'city', $this->city])
            ->andFilterWhere(['like', 'country', $this->country])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'business_licence', $this->business_licence])
            ->andFilterWhere(['like', 'contact_person', $this->contact_person])
            ->andFilterWhere(['like', 'file', $this->file])
            ->andFilterWhere(['like', 'company_username', $this->company_username])
            ->andFilterWhere(['like', 'password', $this->password])
            ->andFilterWhere(['like', 'create_by', $this->create_by]);

        return $dataProvider;
    }
}
