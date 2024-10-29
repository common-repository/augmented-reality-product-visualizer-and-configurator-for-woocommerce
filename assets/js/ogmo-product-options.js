const ogmoIdToken = ogmo_data_object.id_token;
const ogmoUserId = ogmo_data_object.user_id;
const postId = ogmo_data_object.post_id;
const ogmoAuthserviceUrl = ogmo_data_object.ogmo_authservice_url;
const ogmoRefreshToken = ogmo_data_object.refresh_token;
const ogmoConsumerKey = ogmo_data_object.consumer_key;
const ogmoConsumerSecret = ogmo_data_object.consumer_secret;
const ogmoClientId = ogmo_data_object.client_id;
const ogmoApiEndPoint = ogmo_data_object.ogmo_api_endpoint;
const ogmoDashboardUrl = ogmo_data_object.ogmo_dashboard_url;
const savedDesignId = ogmo_data_object.select_option_id;
let select_option_id = savedDesignId;
let createDesignResponse = {};
let selectDesignBodyObj = {};
let uploadDesignBodyObj = {};
let isUploadDesign = false;
let isUnlinkDesign = false;
const backendUrl = ogmoApiEndPoint;
const serverUrl = window.location.protocol + '//' + window.location.host;
const apiUrl = {
    createDesignUrl: backendUrl + "designs",
    uploadAssetUrl: backendUrl + "common/uploadAsset",
    selectOptionLoadUrl: backendUrl + "designs?userId=" + ogmoUserId,
    updateDesignUrl: backendUrl + "designs",
    updateTokenHttpUrl: serverUrl + "/wordpress/wp-json/ogmo/v3/updateIdToken",
    updateTokenHttspUrl: serverUrl + "/wp-json/ogmo/v3/updateIdToken",
    removeAssetUrl: backendUrl + "common/removeAsset"
};
const apiMethod = {
    post: 'POST',
    get: 'GET',
    put: 'PUT'
};

const apiHeader = { 'Content-type': 'application/json; charset=UTF-8' };

function productDesignUnlink() {
    isUnlinkDesign = true;
    document.getElementById('ogmo-designUnlink').disabled = true;
    selectElement("_select_field", "");
    jQuery('#_select_field').select2({ width: "24vw" });
}

function selectElement(id, valueToSelect) {
    let element = document.getElementById(id);
    element.value = valueToSelect;
}

async function selectOptionLoad() {
    try {
        const select_option = await ogmoApi(apiUrl.selectOptionLoadUrl, apiMethod.get, apiHeader, null);

        options_text = [];
        options_id = [];
        selectDesignBodyObj = select_option.data.designs;
        for (var data in select_option.data.designs) {
            for (var key in select_option.data.designs[data]) {
                if (key == "designName") {
                    options_text.push(select_option.data.designs[data][key]);
                }
                if (key == "designId") {
                    options_id.push(select_option.data.designs[data][key]);
                }
            }
        }

        for (num_option = 0; num_option <= options_text.length - 1; num_option++) {
            var opt = document.createElement("OPTION");
            opt.text = options_text[num_option];
            opt.value = options_id[num_option];
            document.getElementById("_select_field").options.add(opt);
        }
        selectElement('_select_field', select_option_id);
        document.getElementById('ogmo-display_design_name').innerHTML = null;
        document.getElementById('ogmo-display_design_name').innerHTML = getDesignName();
    } catch (err) {
        console.log('Error:', err);
    }
}

function checkChangeDesignName() {
    var option_id = document.getElementById("_select_field").value;
    if (savedDesignId == option_id) {
        return false;
    }
    return true;
}
function createUpdateDesignJsonObj(obj, downloadAssetPath) {
    obj["sceneInfo"]["modelPath"] = downloadAssetPath;
    uploadDesignBodyObj = obj;
    return JSON.stringify(uploadDesignBodyObj);
}

window.onload = function () {
    if (savedDesignId) {
        document.getElementById('file-upload').style.display = 'block';
        document.getElementById('ogmo-edit-in-3d').style.display = 'flex';
        document.getElementById('ogmo-designUnlink').style.display = 'flex';
    }
    getDesignV2(savedDesignId);
    selectOptionLoad();
    document.getElementById("ogmo-designGlbFile").onchange = function () {
        if (!isUploadDesign) {
            glbUploadFunction();
        } else {
            replaceGlbUploadFunction();
        }
    };
    jQuery('#_select_field').select2({ width: "24vw" });
    jQuery("#_select_field").on('select2:select', function (e) {
        var id = this.value;
        select_option_id = id;
        if(savedDesignId==id){
            document.getElementById('ogmo-designUnlink').disabled = false;
        }else{
            document.getElementById('ogmo-designUnlink').disabled = true;
        }
        document.getElementById('ogmo-display_design_name').innerHTML = null;
        document.getElementById('ogmo-display_design_name').innerHTML = getDesignName();
        getDesignV2(id);
        document.getElementById('ogmo-edit-in-3d').style.display = 'flex';
    });
};

function chooseView() {
    var selected_view = document.getElementById("ogmo-select-view");
    if (selected_view.value == "upload") {
        uploadDesignView();
    } else if (selected_view.value == "choose") {
        getDesignV2(savedDesignId);
        chooseDesignView();
    }
};

function chooseDesignView() {
    if (savedDesignId) {
        document.getElementById('ogmo-designUnlink').style.display = 'flex';
    }
    document.getElementById("ogmo-info_div").style.display = "flex";
    document.getElementById("ogmo-infoDivOther").style.display = "none";
};

function uploadDesignView() {
    document.getElementById('ogmo-designUnlink').style.display = 'none';
    if (savedDesignId) {
        document.getElementById('ogmo-edit-in-3d').style.display = 'flex';
    }
    var passSceneInfo = new CustomEvent("passSceneInfo", { "detail": { "path": null } });
    document.dispatchEvent(passSceneInfo);

    document.getElementById("ogmo-info_div").style.display = "none";
    document.getElementById("ogmo-infoDivOther").style.display = "block";
    document.getElementById('file-upload').style.display = 'block';
    document.getElementById('ogmo-replace_glb').style.display = 'none';
    document.getElementById('ogmo-upload-progress').style.display = 'none';
    document.getElementById("ogmo-dashed-box").classList.add("ogmo-spaced-border");
    document.getElementById("ogmo-dashed-box").setAttribute("style", "display:flex; flex-direction: column; justify-content: center; align-items:center; border-radius:6px; width:100%; height:27vh; margin-top:15px");
};

async function updateDesignPublish(designId, designObj) {
    try {
        const update_design = await ogmoApi(apiUrl.updateDesignUrl + "/" + designId, apiMethod.put, apiHeader, designObj);
    } catch (error) {
        console.log('Error:', error);
    }
}

function setProductPage() {
    document.getElementById("ogmo-edit-in-3d").href = ogmoDashboardUrl + "editor/" + select_option_id;
}
function close_error() {
    document.getElementById("ogmo-upload_error_div").style.display = 'none';
}

async function replaceGlbUploadFunction() {
    document.getElementById('ogmo-upload-error').style.display = 'none';
    document.getElementById('ogmo-replace_error_div').style.display = 'none';
    if (document.getElementById("ogmo-designGlbFile").files.length == 0) { return; }
    if (document.getElementById("ogmo-designGlbFile").value.split('.')[1] !== "glb") {
        document.getElementById('ogmo-replace_error_div').style.display = 'block';
        document.getElementById("ogmo-dashed-box").style.height = "86px";
        document.getElementById('ogmo-replace_error_text').innerHTML = "Invalid file type";
        return;
    }
    if (document.getElementById("ogmo-designGlbFile").files[0].size > 50 * 1024 * 1024) {
        document.getElementById('ogmo-replace_error_text').innerHTML = "";
        document.getElementById('ogmo-replace_error_div').style.display = 'block';
        document.getElementById("ogmo-dashed-box").style.height = "86px";
        document.getElementById('ogmo-replace_error_text').innerHTML = "File exceeds the maximum size";
        return;
    }
    try {
        const full_Path = document.getElementById("ogmo-designGlbFile").value.split('.')[0];
        const file_format = document.getElementById("ogmo-designGlbFile").value.split('.')[1];
        const file_name = full_Path.replace(/^.*[\\\/]/, '');
        const file = document.getElementById("ogmo-designGlbFile").files[0];
        document.getElementById("ogmo-dashed-box").classList.add("ogmo-spaced-border");
        document.getElementById("ogmo-dashed-box").setAttribute("style", "display:flex; flex-direction: column; justify-content: center; align-items:center; border-radius:6px; width:60%; height:90px;");
        document.getElementById('ogmo-upload-progress').setAttribute("style", "display:flex; flex-direction: column; width:75%;");
        document.getElementById('ogmo-progressbar-box').setAttribute("style", "display:flex; background-color:#C4C4C4; border: 1px solid black; height:10px;");
        document.getElementById('file-upload').style.display = 'none';
        document.getElementById("ogmo-designGlbFile").disabled = true;
        document.getElementById('ogmo-display_design_name').innerHTML = file.name;
        const asset_name = file_name + "." + file_format;
        createDesignResponse["designName"] = file_name;
        const design_id = createDesignResponse["designId"];
        const oldModelPath = createDesignResponse["sceneInfo"]["modelPath"]

        const upload_asset_body = JSON.stringify({
            "dataType": "user-data",
            "userId": ogmoUserId,
            "designId": design_id,
            "assetType": "design",
            "assetName": asset_name
        });
        const upload_asset = await ogmoApi(apiUrl.uploadAssetUrl, apiMethod.post, apiHeader, upload_asset_body);
        const upload_asset_path = upload_asset["data"]["uploadUrl"];
        const download_asset_path = upload_asset["data"]["downloadUrl"];

        // const upload_asset_presign_url =  await fetch(upload_asset_path, { method: apiMethod.put, body: file }) .then((responseJson) => { console.log("Succses") }) .catch((error) => { console.log(error) });

        await jQuery.ajax({
            xhr: function () {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function (evt) {
                    var percentComplete = evt.loaded / evt.total;
                    percentComplete = parseInt(percentComplete * 100);
                    document.getElementById("ogmo-uploadProgressBar").setAttribute("style", "display:block; background-color:blue; border:1px solid blue; height:8px; width:" + percentComplete + "%");
                }, false);
                return xhr;
            },
            url: upload_asset_path,
            type: apiMethod.put,
            data: file,
            processData: false,
            contentType: apiHeader,
            success: function () {
                console.log("success");
            }
        });

        const update_design = await ogmoApi(apiUrl.updateDesignUrl + "/" + createDesignResponse["designId"], apiMethod.put, apiHeader, createUpdateDesignJsonObj(createDesignResponse, download_asset_path));
        const remove_asset_body = JSON.stringify({
            "userId": ogmoUserId,
            "url": oldModelPath
        });
        const remove_asset = await ogmoApi(apiUrl.removeAssetUrl, apiMethod.post, apiHeader, remove_asset_body);

        document.getElementById("ogmo-designGlbFile").disabled = false;
        document.getElementById('file-upload').style.display = 'none';

        document.getElementById("ogmo-progressbar-box").setAttribute("style", "display:none;");
        document.getElementById("ogmo-uploadProgressBar").setAttribute("style", "display:none;");
        document.getElementById("ogmo-dashed-box").classList.remove("ogmo-spaced-border");
        document.getElementById("ogmo-dashed-box").setAttribute("style", "display:flex; flex-direction: column; justify-content: center; align-items:center; border-radius:6px; border:1px solid blue; width:50%; height:40px;");
        document.getElementById('ogmo-replace_glb').style.display = 'flex';
        document.getElementById('ogmo-edit-in-3d').style.display = 'flex';

        getDesignV2(design_id);

        document.getElementById("ogmo-designGlbFile").value = "";
        document.getElementById("_select_field").innerHTML = "";
        select_option_id = createDesignResponse["designId"];
        selectOptionLoad();
        isUploadDesign = true;
    } catch (error) {
        document.getElementById('ogmo-upload-error').style.display = 'block';
        document.getElementById("ogmo-designGlbFile").disabled = false;
        document.getElementById('ogmo-upload-progress').style.display = 'none';
        document.getElementById('ogmo-replace_glb').style.display = 'none';
        document.getElementById('ogmo-progressbar-box').style.display = 'none';
        document.getElementById("ogmo-dashed-box").classList.add("ogmo-spaced-border");
        document.getElementById("ogmo-dashed-box").setAttribute("style", "display:flex; flex-direction: column; justify-content: center; align-items:center; border-radius:6px; width:100%; height:27vh; margin-top:15px");
        document.getElementById('file-upload').style.display = 'block';
        console.log('Error:', error);
    }
}

async function glbUploadFunction(droppedFile) {
    document.getElementById('ogmo-upload-error').style.display = 'none';
    if(droppedFile){
        document.getElementById("ogmo-designGlbFile").files = droppedFile;
    }
    if (document.getElementById("ogmo-designGlbFile").files.length == 0) { return; }
    if (document.getElementById("ogmo-designGlbFile").value.split('.')[1] !== "glb") {
        document.getElementById('ogmo-upload_error_div').style.display = 'block';
        document.getElementById('ogmo-upload_error_text').innerHTML = "Invalid file type";
        return;
    }
    if (document.getElementById("ogmo-designGlbFile").files[0].size > 50 * 1024 * 1024) {
        document.getElementById('ogmo-upload_error_text').innerHTML = "";
        document.getElementById('ogmo-upload_error_div').style.display = 'block';
        document.getElementById('ogmo-upload_error_text').innerHTML = "File exceeds the maximum size";
        return;
    }
    try {
        const full_Path = document.getElementById("ogmo-designGlbFile").value.split('.')[0];
        const file_format = document.getElementById("ogmo-designGlbFile").value.split('.')[1];
        const file_name = full_Path.replace(/^.*[\\\/]/, '');
        const file = document.getElementById("ogmo-designGlbFile").files[0];
        document.getElementById("ogmo-dashed-box").classList.add("ogmo-spaced-border");
        document.getElementById("ogmo-dashed-box").setAttribute("style", "display:flex; flex-direction: column; justify-content: center; align-items:center; border-radius:6px; width:60%; height:90px;");
        document.getElementById('ogmo-upload-progress').setAttribute("style", "display:flex; flex-direction: column; width:75%;");
        document.getElementById('ogmo-progressbar-box').setAttribute("style", "display:flex; background-color:#C4C4C4; border: 1px solid black; height:10px;");
        document.getElementById('file-upload').style.display = 'none';
        document.getElementById("ogmo-designGlbFile").disabled = true;
        document.getElementById('ogmo-display_design_name').innerHTML = file.name;
        const create_design_body = JSON.stringify({
            "userId": ogmoUserId,
            "designName": file_name,
            "publishProducts": [],
            "thumbnail": "", "sceneInfo":
            {
                "modelPath": "",
                "cameraPosition": null,
                "environmentMapID": "",
                "environmentMapPath": "",
                "ackgroundColor": "",
                "ambientIntensity": null,
                "shadowOpacity": null,
                "shadowBlur": null,
                "shadowBias": null
            },
            "enableDesign": true,
            "updatedAt": "",
            "createdAt": "",
            "shortId": ""
        });
        const create_design = await ogmoApi(apiUrl.createDesignUrl, apiMethod.post, apiHeader, create_design_body);
        this.create_design = create_design;
        createDesignResponse = create_design["data"];
        const asset_name = create_design["data"]["designName"] + "." + file_format;
        const design_id = create_design["data"]["designId"];

        const upload_asset_body = JSON.stringify({
            "dataType": "user-data",
            "userId": ogmoUserId,
            "designId": design_id,
            "assetType": "design",
            "assetName": asset_name
        });
        const upload_asset = await ogmoApi(apiUrl.uploadAssetUrl, apiMethod.post, apiHeader, upload_asset_body);
        const upload_asset_path = upload_asset["data"]["uploadUrl"];
        const download_asset_path = upload_asset["data"]["downloadUrl"];

        // const upload_asset_presign_url =  await fetch(upload_asset_path, { method: apiMethod.put, body: file }) .then((responseJson) => { console.log("Succses") }) .catch((error) => { console.log(error) });

        await jQuery.ajax({
            xhr: function () {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function (evt) {
                    var percentComplete = evt.loaded / evt.total;
                    percentComplete = parseInt(percentComplete * 100);
                    document.getElementById("ogmo-uploadProgressBar").setAttribute("style", "display:block; background-color:blue; border:1px solid blue; height:8px; width:" + percentComplete + "%");
                }, false);
                return xhr;
            },
            url: upload_asset_path,
            type: apiMethod.put,
            data: file,
            processData: false,
            contentType: apiHeader,
            success: function () {
                console.log("success");
            }
        });

        const update_design = await ogmoApi(apiUrl.updateDesignUrl + "/" + createDesignResponse["designId"], apiMethod.put, apiHeader, createUpdateDesignJsonObj(createDesignResponse, download_asset_path));
        document.getElementById("ogmo-designGlbFile").disabled = false;
        document.getElementById('file-upload').style.display = 'none';

        document.getElementById("ogmo-progressbar-box").setAttribute("style", "display:none;");
        document.getElementById("ogmo-uploadProgressBar").setAttribute("style", "display:none;");
        document.getElementById("ogmo-dashed-box").classList.remove("ogmo-spaced-border");
        document.getElementById("ogmo-dashed-box").setAttribute("style", "display:flex; flex-direction: column; justify-content: center; align-items:center; border-radius:6px; border:1px solid blue; width:50%; height:40px;");
        document.getElementById('ogmo-replace_glb').style.display = 'flex';
        document.getElementById('ogmo-edit-in-3d').style.display = 'flex';

        getDesignV2(design_id);

        document.getElementById("ogmo-designGlbFile").value = "";
        document.getElementById("_select_field").innerHTML = "";
        select_option_id = createDesignResponse["designId"];
        selectOptionLoad();
        isUploadDesign = true;
    } catch (error) {
        document.getElementById('ogmo-upload-error').style.display = 'block';
        document.getElementById("ogmo-designGlbFile").disabled = false;
        document.getElementById('ogmo-upload-progress').style.display = 'none';
        document.getElementById('ogmo-replace_glb').style.display = 'none';
        document.getElementById('ogmo-progressbar-box').style.display = 'none';
        document.getElementById("ogmo-dashed-box").classList.add("ogmo-ogmo-spaced-border");
        document.getElementById("ogmo-dashed-box").setAttribute("style", "display:flex; flex-direction: column; justify-content: center; align-items:center; border-radius:6px; width:100%; height:27vh; margin-top:15px");
        document.getElementById('file-upload').style.display = 'block';
        console.log('Error:', error);
    }
}

function handleDrop(evt){
    evt.stopPropagation();
    evt.preventDefault();
    const droppedFile = evt.dataTransfer.files;
    glbUploadFunction(droppedFile);
}
function handleDragOver(evt) {
    evt.stopPropagation();
    evt.preventDefault();
}
var dropZone = document.getElementById('ogmo-dashed-box');
dropZone.addEventListener('dragover', handleDragOver);
dropZone.addEventListener('drop', handleDrop);

function getDesignV2(designID) {
    var passSceneInfo = new CustomEvent("passSceneInfo", { "detail": { "path": null } });
    document.dispatchEvent(passSceneInfo);

    var requestOptions = {
        method: 'GET',
        redirect: 'follow'
    };
    if (designID !== undefined && designID !== "") {
        fetch(`${backendUrl}designs/platform/${designID}`, requestOptions)
            .then(response => response.json())
            .then(result => {
                var passSceneInfo = new CustomEvent("passSceneInfo", { "detail": { "path": JSON.stringify(result.data) } });
                document.dispatchEvent(passSceneInfo);
            })
            .catch(error => {
                console.log('error', error);
                document.getElementById('ogmo-upload-error').style.display = 'block';
            });
    }
}

function getDesignName() {
    var select_field = document.getElementById("_select_field");
    var design_id = select_field.value;
    var design_name = select_field.options[select_field.selectedIndex].text + ".glb";
    return design_name;
}

document.getElementById("publish").onclick = function () {
    if (checkChangeDesignName() == true || isUploadDesign == true) {
        var select_field = document.getElementById("_select_field");
        var design_id = select_field.value;
        var design_name = select_field.options[select_field.selectedIndex].text;
        var obj = {};

        if (Object.keys(uploadDesignBodyObj).length === 0 && Object.keys(selectDesignBodyObj).length === 0) {
            return;
        } else {
            var publishProducts = [postId];
            if (Object.keys(selectDesignBodyObj).length != 0) {
                for (var data in selectDesignBodyObj) {
                    if (selectDesignBodyObj[data]["designId"] == savedDesignId) {
                        var previous_obj = selectDesignBodyObj[data];
                        previous_obj["publishProducts"] = [];
                        previous_obj["unLinkedProduct"] = postId;
                        updateDesignPublish(savedDesignId, JSON.stringify(previous_obj));
                    }

                    if (selectDesignBodyObj[data]["designId"] == design_id) {
                        if (!isUnlinkDesign) {
                            obj = selectDesignBodyObj[data];
                            obj["userId"] = ogmoUserId;
                            obj["designName"] = design_name;
                            obj["publishProducts"] = publishProducts;
                            obj["linkedProduct"] = postId;
                            obj = JSON.stringify(obj);
                            updateDesignPublish(design_id, obj);
                        }
                    }
                }
            }
            if (Object.keys(uploadDesignBodyObj).length != 0) {
                if (design_name.localeCompare(uploadDesignBodyObj["designName"]) == 0) {
                    if (!isUnlinkDesign) {
                        obj = uploadDesignBodyObj;
                        obj["userId"] = ogmoUserId;
                        obj["designName"] = design_name;
                        obj["publishProducts"] = publishProducts;
                        obj["linkedProduct"] = postId;
                        obj = JSON.stringify(obj);
                        updateDesignPublish(design_id, obj);
                    }
                }
            }
        }
    }
}