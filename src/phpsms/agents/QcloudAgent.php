<?php

namespace Toplan\PhpSms;

/**
 * Class AlidayuAgent
 *
 * @property string $appid
 * @property string $appkey
 * @property string $sendUrl
 */
class QcloudAgent extends Agent
{

    public function sendSms($to, $content, $tempId, array $data)
    {
        $this->sendTemplateSms($to, $tempId, $data);
    }


    public function sendContentSms($to, $content)
    {
        $params['to'] = $to;
        $params['content'] = $content;
        $this->request($params);
    }
    public function sendTemplateSms($to, $tempId, array $data)
    {
        $params['to'] = $to;
        $params['tempId'] = (int) $tempId;
        $params['tempdata'] = $data;
        $this->request($params);
    }
    protected function request(array $params)
    {
        $randNum = rand(100000, 999999);
        $sendUrl = $this->sendUrl . "?sdkappid=" . $this->appid . "&random=" . $randNum;
        if (isset($params['content'])) {
            $params = $this->createContentParams($params);
        } elseif (isset($params['tempId'])) {
            $params = $this->createTemplateParams($params);
        }
        $result = $this->QcloudCurl($sendUrl, $params);
        $this->setResult($result);
    }
    protected function createContentParams(array $params)
    {
        $tel = new \stdClass();
        $tel->nationcode = '86';
        $tel->phone = $params['to'];
        $jsondata = new \stdClass();
        $jsondata->tel = $tel;
        $jsondata->type = '0';
        $jsondata->msg = $params['content'];
        $jsondata->sig = md5($this->appkey . $params['to']);
        $jsondata->extend = '';     // 根据需要添加，一般保持默认
        $jsondata->ext = '';        // 根据需要添加，一般保持默认
        $params = json_encode($jsondata);
        return $params;
    }
	
    protected function createTemplateParams(array $params)
    {
        $tel = new \stdClass();
        $tel->nationcode = '86';
        $tel->phone = $params['to'];
        $jsondata = new \stdClass();
        $jsondata->tel = $tel;
        $jsondata->type = '0';
        $jsondata->tpl_id = $params['tempId'];
        $jsondata->params = [$params['tempdata']['code'], (string) $params['tempdata']['minutes']];
        $jsondata->sig = md5($this->appkey . $params['to']);
        $jsondata->extend = '';     // 根据需要添加，一般保持默认
        $jsondata->ext = '';        // 根据需要添加，一般保持默认
        $params = json_encode($jsondata);
        return $params;
    }

    public function voiceVerify($to, $code, $tempId, array $data)
    {

    }
	
    protected function QcloudCurl($url, $optData)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $optData);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        if ($response === false) {
            $request = false;
            $response = curl_getinfo($ch);
        }
        curl_close($ch);
        $response = json_decode($response, true);
	    
        return compact('request', 'response');
    }
    protected function genSign($params)
    {
        //
    }
    protected function setResult($result)
    {
        if ($result['response']['result'] === '0') {
            $this->result(Agent::SUCCESS, true);
            $this->result(Agent::INFO, $result['response']['ext']);
            $this->result(Agent::CODE, 1111);
        } else {
            $this->result(Agent::SUCCESS, false);
            $this->result(Agent::INFO, $result['response']['errmsg']);
            $this->result(Agent::CODE, 0000);
        }
    }
    protected function genResponseName($method)
    {
    }

    protected function getTempDataString(array $data)
    {
        $data = array_map(function ($value) {
            return (string) $value;
        }, $data);

        return json_encode($data);
    }
}
