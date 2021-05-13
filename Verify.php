<?php

namespace app\http\middleware;

use app\common\Parser;
use Firebase\JWT\JWT;
use think\exception\HttpResponseException;

/**
 * 基于tp5.1的中间件，以及ThinkPHP-ApiDoc注释接口文档
 * 通过识别注释的param实现简单的require判断
 */
class Verify
{
    /**
     * 填写需要参与判断的控制器 app\\api\\Login
     */
    protected $config = [
    ];


    //获取注释参数
    public function getAnnotationParam()
    {
        $controllers = config('apidoc.controllers')?config('apidoc.controllers'):$this->config;
        $actions=[];
        $parser = new Parser();
        foreach ($controllers as $k => $class) {
            $class = "app\\" . $class;
            if (class_exists($class)) {
                $reflection = new \ReflectionClass($class);
                $doc_str = $reflection->getDocComment();
                // 获取当前控制器Class的所有方法
                $method = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
                $filter_method = array_merge(['__construct'], $this->config['filter_method']);

                foreach ($method as $j=>$action) {
                    // 过滤不解析的方法
                    if (!in_array($action->name, $filter_method)) {
                        // 获取当前方法的注释
                        $actionDocStr = $action->getDocComment();
                        if ($actionDocStr) {
                            // 解析当前方法的注释
                            $action_doc = $parser->parseAction($actionDocStr);
                            if (array_key_exists('title', $action_doc)) {
                                $actions[] = $action_doc;
                            }
                        }
                    }
                }
            }
        }
        return $actions;
    }

    public function handle($request, \Closure $next)
    {
        $AnnotationParam = $this->getAnnotationParam();
        $verify = dataGroup($AnnotationParam, 'url'); #把数字转换成键值为地址的数组
        $path = '/'.$request->path(); #拿到访问的地址
        $param = $request->param(); #拿到参数
        array_shift($param); #第一条是地址，弹出不要
        if (array_key_exists($path, $verify)) {
            $result = [];
            array_map(function ($value) use (&$result) {
                if ($value['require'] == 1) {
                    $value['require'] = 'require';
                }
                $result[] = $value;
            }, $verify[$path][0]['param']);

            $validate = new \think\Validate;
            $rule = array_column($result, 'require', 'name'); #构建tp的validate的自定义规则需要的数组格式
            if (in_array('token', array_values($verify[$path][0]['header'][0]))) {
                array_unshift($rule, ['token'=>'require']);
            }
            $validate->rule($rule);
            if (!$validate->check($param)) {
                throw new HttpResponseException(json(['code'=>0,'msg'=>$validate->getError(),'data'=>[]]));
            }
        }
        return $next($request);
    }
}
