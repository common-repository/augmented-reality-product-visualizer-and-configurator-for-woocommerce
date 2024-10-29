async function updateIdToken(){
    if(window.location.protocol == "http:"){
            const oauth = OAuth({
                    consumer: {
                            key: ogmoConsumerKey,
                            secret: ogmoConsumerSecret,
                    },
                    signature_method: 'HMAC-SHA1',
                    hash_function(base_string, key) {
                            return CryptoJS.HmacSHA1(base_string, key).toString(CryptoJS.enc.Base64)
                    },
            })
            const request_data = {
                    url: apiUrl.updateTokenHttpUrl,
                    method: 'POST',
                    data: {  "id_token": ogmoIdToken },
            }
            const token = {
                    key: '',
                    secret: '',
            }
            header_auth = oauth.toHeader(oauth.authorize(request_data, token))
            headers = {"Accept":"application/json"}
            headers[Object.keys(header_auth)[0]] = Object.values(header_auth)[0]
            var formData = new FormData();
            formData.append(Object.keys(request_data.data)[0], Object.values(request_data.data)[0]);
            await fetch(request_data.url, {
                    method: request_data.method,
                    headers: headers,
                    body: formData
            }).then(response => response.json());
    }else if(window.location.protocol == "https:"){
            const request_data = {
                    url: apiUrl.updateTokenHttspUrl ,
                    method: 'POST',
                    data: {  "id_token": ogmoIdToken },
            }
            const  consuder_values =  btoa(ogmoConsumerKey +":"+ ogmoConsumerSecret);
            headers = {"Accept":"application/json","Content-Type":"application/json"}
            headers['Authorization'] = 'Basic '+consuder_values
            await fetch(request_data.url, {
                    method: request_data.method,
                    headers: headers,
                    body: JSON.stringify(request_data.data)
            }).then(response => response.json());
    }
}

async function ogmoApi(url, method, header, body){
    return new Promise(async (resolve, reject) => {
            try{
                    header.authorization = ogmoIdToken;
                    let ogmo_api_response = await fetch(url, {
                            method: method,
                            body: body,
                            headers: header
                    }).then(response => response);
                    if (ogmo_api_response.status === 401 || ogmo_api_response.status === 403){
                            const update_token_header = {"Content-Type":"application/x-www-form-urlencoded", "Accept":"application/json"};
                            const update_token_response = await fetch(ogmoAuthserviceUrl,{method: apiMethod.post, body: 'grant_type=refresh_token&client_id='+ogmoClientId+'&refresh_token='+ogmoRefreshToken+'',headers: update_token_header}).then(response => response.json());
                            ogmoIdToken = update_token_response.id_token;
                            header.authorization = ogmoIdToken;
                            await updateIdToken();
                            ogmo_api_response = await fetch(url, {
                                    method: method,
                                    body: body,
                                    headers: header
                            }).then(response => response);
                    }

                    if (ogmo_api_response.ok){
                            return resolve(ogmo_api_response.json());
                    }else{
                            return reject(ogmo_api_response.json())
                    }
            }
            catch(e) {
                    console.log(e);
                    return reject({})
            }
    });
}