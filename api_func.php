//只修改这几项
<?php 
$GITHUB_USERNAME = '';  // 用户名
$GITHUB_REPONAME = '';  // Repository名
$GITHUB_BRANCHNAME = 'main';  // 分支名 我的是main git上传的可能是master 根据情况来
$GITHUB_TOKEN = '';  // TOKEN  获取链接：https://github.com/settings/tokens
$GITHUB_EMAIL = '';  // Github绑定的邮箱
$GITHUB_NAME = '';  // 昵称
//下面无需修改
function callInterfaceCommon($URL, $type, $params, $headers)
{
    $ch = curl_init($URL);
    $timeout = 5;
    if ($headers != "") {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    } else {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    switch ($type) {
        case "GET" :
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            break;
        case "POST":
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            break;
        case "PUT" :
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            break;
        case "PATCH":
            curl_setopt($ch, CULROPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            break;
        case "DELETE":
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            break;
    }
    $result = curl_exec($ch);
    $myfile = fopen("testfile.txt", "w");
    fwrite($myfile, $result);
    if (curl_errno($ch)) {
        return 'Curl Error: ' . curl_error($ch);
    }
    curl_close($ch);
    return 'Success';
}

function upload_file_to_github($abs_filepath, $content)
{
    global $GITHUB_USERNAME, $GITHUB_TOKEN, $GITHUB_NAME;
    global $GITHUB_REPONAME, $GITHUB_BRANCHNAME, $GITHUB_EMAIL;
    $params = sprintf('{"message":"Upload New File", 
                        "branch": "%s", 
                        "content": "%s",
                        "committer": {
                            "name": "%s",
                            "email": "%s"
                        }
                }', $GITHUB_BRANCHNAME, $content, $GITHUB_NAME, $GITHUB_EMAIL);
    $url = sprintf('https://api.github.com/repos/%s/%s/contents/%s',
        $GITHUB_USERNAME, $GITHUB_REPONAME, $abs_filepath);
    $headers = array('User-Agent: ' . $GITHUB_USERNAME, 'Authorization:token ' . $GITHUB_TOKEN);
    $result = callInterfaceCommon($url, "PUT", $params, $headers);
    if ($result == 'Success') {
        $cdnURL = sprintf('https://cdn.jsdelivr.net/gh/%s/%s@%s/%s',
            $GITHUB_USERNAME, $GITHUB_REPONAME, $GITHUB_BRANCHNAME, $abs_filepath);
        $originURL = sprintf('https://raw.githubusercontent.com/%s/%s/%s/%s',
            $GITHUB_USERNAME, $GITHUB_REPONAME, $GITHUB_BRANCHNAME, $abs_filepath);
        $sevenCDN = sprintf('https://raw.sevencdn.com/%s/%s/%s/%s',
            $GITHUB_USERNAME, $GITHUB_REPONAME, $GITHUB_BRANCHNAME, $abs_filepath);
        return $cdnURL . ' ' . $originURL . ' ' . $sevenCDN;
    } else {
        return $result;
    }
}

