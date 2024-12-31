<?php
// 设置响应头为JSON格式
header('Content-Type: application/json');

// 初始化返回结果
$response = [
    'code' => 1,  // 默认返回错误
    'msg' => '抖音联动活动已结束，您可从新截图扫码参与下方小红书联动活动截止1月30日',
];
// 允许的域名
$allowed_domain = '1kkbp6w6sx2qr-env-ddhjwzgo1k.service.douyincloud.run'; // 替换为你自己的域名

// 获取请求的HTTP Referer和Origin
$http_referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$http_origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// 检查HTTP Referer和Origin是否包含允许的域名
if (strpos($http_referer, $allowed_domain) === false && strpos($http_origin, $allowed_domain) === false) {
    // 如果Referer或Origin不包含指定的域名，返回错误
    $response['code'] = 1;
    $response['msg'] = '不允许的来源';
    echo json_encode($response);
    exit;
}

// 文件路径
$file_path = __DIR__ . '/xianuykj115351465.txt';  // 使用绝对路径

// 获取用户的IP地址
$user_ip = $_SERVER['REMOTE_ADDR'];

// 获取当前时间和日期
$current_date = date('Y-m-d'); // 当前日期

// 检查请求方法是否为POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取原始请求体中的数据
    $raw_data = file_get_contents('php://input');

    // 将原始数据解析为键值对
    parse_str($raw_data, $parsed_data);

    // 检查是否是check_receive的请求
    if (isset($parsed_data['route_name']) && $parsed_data['route_name'] === 'check_receive') {
        // 检查文件是否存在
        if (file_exists($file_path)) {
            $existing_data = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $ip_exists = false;
            $ip_date = null;
            $wdsj_data = null;  // 用于存储找到的wdsj数据

            // 遍历文件内容，查找IP
            foreach ($existing_data as $index => $line) {
                if (strpos($line, "IP: " . $user_ip) !== false) {
                    $ip_exists = true;
                    
                    // 如果找到IP，检查下一行的 WDSJ
                    if (isset($existing_data[$index + 2])) {
                        preg_match('/WDSJ: (.+)/', $existing_data[$index + 2], $matches);
                        if (isset($matches[1])) {
                            $wdsj_data = $matches[1];  // 提取WDSJ数据
                        }
                    }

                    // 检查日期
                    if (isset($existing_data[$index + 3])) {
                        preg_match('/Date: (\d{4}-\d{2}-\d{2})/', $existing_data[$index + 3], $matches);
                        if (isset($matches[1])) {
                            $ip_date = $matches[1];  // 提取记录的日期
                        }
                    }
                    break;
                }
            }

            // 如果找到IP，进一步检查日期
            if ($ip_exists) {
                $yesterday = date('Y-m-d', strtotime('-1 day')); // 昨天的日期

                if ($ip_date === $current_date) {
                    $response['code'] = 0;
                    $response['msg'] = '审核通过，明日打开小红书重新观看10个视频后奖励自动发放到游戏：' . $wdsj_data;  // 返回WDSJ数据
                } elseif ($ip_date === $yesterday) {
                    $response['code'] = 3;
                    $response['msg'] = '完成10个不同视频阅读后礼包即可到账，部分用户还需明日再阅读一天';
                } else {
                    $response['code'] = 1;
                    $response['msg'] = '请完成下列任务后再领取';
                }
            }
        }
    } else {
        // 检查是否存在 phone 和 wdsj 数据
        $phone = isset($parsed_data['phone']) ? trim($parsed_data['phone']) : '';
        $wdsj = isset($parsed_data['wdsj']) ? trim($parsed_data['wdsj']) : '';

        if ($phone && $wdsj) {
            // 检查 phone 是否已经在txt文件中
            if (file_exists($file_path)) {
                $existing_data = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                if (in_array("Phone: " . $phone, $existing_data)) {
                    $response['code'] = -2;
                    $response['msg'] = '由于您未曾使用过小红书，需明日再次观看10个视频后奖励自动发放到游戏';
                } else {
                    // 记录 phone 和 wdsj 以及其他数据
                    $log_entry = "IP: " . $user_ip . "\n" . "Phone: " . $phone . "\n" . "WDSJ: " . $wdsj . "\n" . "Date: " . $current_date . "\n" . "-----\n";
                    file_put_contents($file_path, $log_entry, FILE_APPEND);

                    // 设置成功响应
                    $response['code'] = 0;
                    $response['msg'] = '';
                }
            } else {
                // 文件不存在，创建新文件并写入数据
                $log_entry = "IP: " . $user_ip . "\n" . "Phone: " . $phone . "\n" . "WDSJ: " . $wdsj . "\n" . "Date: " . $current_date . "\n" . "-----\n";
                file_put_contents($file_path, $log_entry, FILE_APPEND);

                // 设置成功响应
                $response['code'] = 0;
                $response['msg'] = '';
            }
        } else {
            $response['msg'] = '缺少必要的参数';
        }
    }
} else {
    // 非POST请求，返回错误信息
    $response['msg'] = '请求方法错误';
}

// 返回JSON响应
echo json_encode($response);
?>