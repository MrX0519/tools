<?php

namespace app\http\middleware;

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


    /**
     * 解析控制器方法的注释
     * @param string $doc
     * @return array
     */
    public function parseAction($doc = '')
    {
        if ($doc == '') {
            return false;
        }
        // Get the comment
        if (preg_match('#^/\*\*(.*)\*/#s', $doc, $comment) === false) {
            return false;
        }
        $comment = trim($comment [1]);
        // Get all the lines and strip the * from the first character
        if (preg_match_all('#^\s*\*(.*)#m', $comment, $lines) === false) {
            return false;
        }
        $res = $this->parseActionLines($lines [1]);

        return $res;
    }

    /**
     * 解析方法的注释，将每条字符串，解析成key,value对象，并处理
     * @param $lines
     * @return array|bool
     */
    private function parseActionLines($lines)
    {
        $desc = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                if (strpos($line, '@') === 0) {
                    if (strpos($line, ' ') > 0) {
                        // Get the parameter name
                        $param = substr($line, 1, strpos($line, ' ') - 1);
                        $value = substr($line, strlen($param) + 2); // Get the value
                    } else {
                        $param = substr($line, 1);
                        $value = '';
                    }

                    if ($param == 'param') {
                        $valueObj = $this->formatParam($value);
                        if (!empty($valueObj['params']) && empty($valueObj['name'])) {
                            // 只配置参数，没配置name则直接值为参数
                            if (is_array($valueObj["params"]) && count($valueObj["params"]) > 0) {
                                // 数组则遍历出来
                                foreach ($valueObj["params"] as $paramItem) {
                                    $desc [$param][] = $paramItem;
                                }
                            } else {
                                $desc [$param][] = $valueObj["params"];
                            }
                        } elseif (!empty($valueObj['type']) && $valueObj['type'] === 'tree') {
                            // 类型为tree的
                            $childrenField = "children";
                            if (!empty($valueObj['childrenField'])) {
                                $childrenField=$valueObj['childrenField'];
                            }
                            $childrenDesc = "children";
                            if (!empty($valueObj['childrenDesc'])) {
                                $childrenDesc=$valueObj['childrenDesc'];
                            }
                            $valueObj['params'][] = array(
                                'params'=>$valueObj['params'],
                                'name'=>$childrenField,
                                'type'=>'array',
                                'desc'=>$childrenDesc,
                            );
                            $desc [$param][] = $valueObj;
                        } else {
                            $desc [$param][] = $valueObj;
                        }
                    } elseif ($param == 'return') {
                        $valueObj = $this->formatReturn($value);
                        if (!empty($valueObj['params']) && empty($valueObj['name'])) {
                            // 只配置参数，没配置name则直接值为参数
                            if (is_array($valueObj["params"]) && count($valueObj["params"]) > 0) {
                                // 数组则遍历出来
                                foreach ($valueObj["params"] as $paramItem) {
                                    $desc [$param][] = $paramItem;
                                }
                            } else {
                                $desc [$param][] = $valueObj["params"];
                            }
                        } elseif (!empty($valueObj['type']) && $valueObj['type'] === 'tree') {
                            // 类型为tree的
                            $childrenField = "children";
                            if (!empty($valueObj['childrenField'])) {
                                $childrenField=$valueObj['childrenField'];
                            }
                            $childrenDesc = "children";
                            if (!empty($valueObj['childrenDesc'])) {
                                $childrenDesc=$valueObj['childrenDesc'];
                            }
                            $valueObj['params'][] = array(
                                'params'=>$valueObj['params'],
                                'name'=>$childrenField,
                                'type'=>'array',
                                'desc'=>$childrenDesc,
                            );
                            $desc [$param][] = $valueObj;
                        } else {
                            $desc [$param][] = $valueObj;
                        }
                    } elseif ($param == 'header') {
                        $valueObj = $this->formatHeaders($value);
                        $desc [$param][] = $valueObj;
                    } elseif ($param == 'addField') {
                        // 模型指定添加的字段
                        $valueObj = $this->formatHeaders($value);
                        $desc [$param][] = $valueObj;
                    } else {
                        $desc[$param]=$value;
                    }
                }
            }
        }
        return $desc;
    }

    // 处理Param的解析
    private function formatParam($string)
    {
        $string = $string." ";
        if (preg_match_all('/(\w+):(.*?)[\s\n]/s', $string, $meatchs)) {
            $param = [];
            foreach ($meatchs[1] as $key=>$value) {
                $paramKey = $meatchs[1][$key];
                $value = $meatchs[2][$key];
                if ($paramKey == "params") {
                    // 处理对象类型
                    $value = $this->parseObjectLine($value);
                } elseif ($paramKey == "ref") {
                    // 处理引用
                    $value = $this->parseRefLine($value, "param");
                    $paramKey="params";
                } elseif ($paramKey == "field" && !empty($param["params"])) {
                    // 只取模型指定字段
                    $param["params"] = $this->filterModelTableField($param["params"], $value, "field");
                } elseif ($paramKey == "withoutField" && !empty($param["params"])) {
                    // 排除模型指定字段
                    $param["params"] = $this->filterModelTableField($param["params"], $value, "withoutField");
                }
                $param[$paramKey] =$value;
            }
            return $param;
        } else {
            return ''.$string;
        }
    }

    // 处理Return的解析
    private function formatReturn($string)
    {
        $string = $string." ";
        if (preg_match_all('/(\w+):(.*?)[\s\n]/s', $string, $meatchs)) {
            $param = [];
            foreach ($meatchs[1] as $key=>$value) {
                $paramKey = $meatchs[1][$key];
                $value = $meatchs[2][$key];
                if ($paramKey == "params") {
                    // 处理对象类型
                    $value = $this->parseObjectLine($value);
                } elseif ($paramKey == "ref") {
                    // 处理引用
                    $value = $this->parseRefLine($value, "return");
                    if (!empty($value) && is_array($value) && count($value)===1) {
                        if (!empty($value[0]) && !empty($value[0]['params'])) {
                            $value=$value[0]['params'];
                        }
                    }
                    $paramKey="params";
                } elseif ($paramKey == "field" && !empty($param["params"])) {
                    // 只取模型指定字段
                    $param["params"] = $this->filterModelTableField($param["params"], $value, "field");
                } elseif ($paramKey == "withoutField" && !empty($param["params"])) {
                    // 排除模型指定字段
                    $param["params"] = $this->filterModelTableField($param["params"], $value, "withoutField");
                }
//                if ($paramKey == "type" && $value === 'tree') {
//                    // 数据结构为树形结构
//                    $value =$param;
//                }
                $param[$paramKey] =$value;
            }
            return $param;
        } else {
            return ''.$string;
        }
    }

    // 处理Headers的解析
    private function formatHeaders($string)
    {
        $string = $string." ";
        if (preg_match_all('/(\w+):(.*?)[\s\n]/s', $string, $meatchs)) {
            $param = [];
            foreach ($meatchs[1] as $key=>$value) {
                $paramKey = $meatchs[1][$key];
                $value = $meatchs[2][$key];
                $param[$paramKey] =$value;
            }
            return $param;
        } else {
            return ''.$string;
        }
    }

    //获取注释参数
    public function getAnnotationParam()
    {
        $controllers = config('apidoc.controllers')?config('apidoc.controllers'):$this->config;
        $actions=[];
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
                            $action_doc = $this->parseAction($actionDocStr);
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
            $validate->rule($rule);
            if (!$validate->check($param)) {
                throw new HttpResponseException(json(['code'=>0,'msg'=>$validate->getError(),'data'=>[]]));
            }
        }
        return $next($request);
    }
}
