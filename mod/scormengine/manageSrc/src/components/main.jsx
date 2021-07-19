import React, { Component, useState, useEffect } from "react";
import Pages from "./pages";

function DeleteConfirm({promptText,okFunc,cancelFunc})
{

  return  <div class="modal fade show" style={{    background: "rgba(0,0,0,.6)"}} tabIndex="-1" role="dialog">
  <div className="modal-dialog" role="document">
  <div className="modal-content">
    <div className="modal-header">
      <button type="button" className="close" onClick={()=>cancelFunc()} data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 className="modal-title" id="myModalLabel">Delete Package?</h4>
    </div>
    <div className="modal-body">
      {promptText}
    </div>
    <div className="modal-footer">
      <button type="button" className="btn btn-default" onClick={()=>cancelFunc()} data-dismiss="modal">Cancel</button>
      <button type="button" id="btn_ok_1" onClick={()=>okFunc()} className="btn btn-primary">OK</button>
    </div>
  </div>
</div>
</div>

}

function Attempts({ API, selected }) {
  let [attempts, setAttempts] = useState([]);
  let [page, setPage] = useState(0);
  let [total, setTotal] = useState([]);

  function getAttempts() {
    API.ajax({
      url: "/mod/scormengine/service/getAttempts.php?cid=" + selected + "&page="+page,
      method: "GET",
      success: (body, status, xhr) => {
        let arr = [];
        for (let i in body.registrations) {
          arr.push(body.registrations[i]);
        }
        setAttempts(arr);
        setTotal(body.count);
      }
    });
  }  

  function deleteAttempt(a) {
    API.ajax({
      url: "/mod/scormengine/service/deleteAttempt.php?rid=" + a.registration,
      method: "GET",
      success: (body, status, xhr) => {
        getAttempts();
      }
    });
  }
  useEffect(() => getAttempts(), []);
  return (
    <div>
      <table className="table">
        <thead className="thead-dark">
          <tr>
            <th scope="col">Email</th>
            <th scope="col">Username</th>
            <th scope="col">Completion</th>
            <th scope="col">Success</th>
            <th scope="col">Progress</th>
            <th scope="col">Score</th>
            <th scope="col">Duration</th>
            <th scope="col"></th>
          </tr>
        </thead>
        <tbody className="table-striped">
          {attempts.map(i => (
            <tr className={i.uuid === selected ? "table-primary" : ""}>
              <th scope="row">{i.user.email}</th>
              <td>{i.user.username}</td>
              <td>{i.completion}</td>
              <td>{i.success}</td>
              <td>{i.progress}</td>
              <td>{i.score}</td>
              <td>{i.duration}</td>
              <td>
                <div class="dropdown">
                  <button
                    class="btn btn-secondary dropdown-toggle"
                    type="button"
                    id={"dropdownMenuButton" + i.id}
                    data-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                  >
                    Actions
                  </button>
                  <div
                    class="dropdown-menu"
                    aria-labelledby={"dropdownMenuButton" + i.id}
                  >
                    <a class="dropdown-item" onClick={() => deleteAttempt(i)}>
                      Delete
                    </a>
                   
                  </div>
                </div>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
      <Pages currentPage={page} onPage={setPage} total={total} perPage={10}></Pages>
    </div>
  );
}

function List({ API, selected, setSelected }) {
  let [packages, setPackages] = useState([]);
  let [page, setPage] = useState(0);
  let [total, setTotal] = useState([]);
  let [search, setSearch] = useState('');
  let [toDelete, setToDelete] = useState();
  function getPackages() {
    API.ajax({
      url: "/mod/scormengine/service/getPackages.php?page=" + (page ) + "&search=" + search,
      method: "GET",
      success: (body, status, xhr) => {
        let arr = [];
        for (let i in body.packages) {
          arr.push(body.packages[i]);
        }
        setPackages(arr);
        setTotal(body.count);
      }
    });
  }

  function _deleteCourse(c) {
    API.ajax({
      url: "/mod/scormengine/service/deletePackage.php?cid=" + c.uuid,
      method: "GET",
      success: (body, status, xhr) => {
        getPackages();
      }
    });
  }
  function deleteCourse(c) {
    setToDelete(c)
  }
  useEffect(() => getPackages(), [page, search]);
  return (
    <div>
      {toDelete && <DeleteConfirm 
                    promptText="Are you sure you want to delete this package? It will no longer be available to any activities in the LMS."
                    okFunc={()=>{
                      _deleteCourse(toDelete);
                      setToDelete();
                    }}
                    cancelFunc={setToDelete}
                    >

                  </DeleteConfirm>
      }
      <div className="container text-center">
          <div className="input-group rounded" style={{    borderRadius: ".25rem!important",
    display: "flex",
    justifyContent: "center",
    margin: "auto",
    padding: "1em"}}>
            <div className="form-outline">
              <input type="search" id="form1" className="form-control" />
            
            </div>
            <button type="button" onClick={() => {setPage(0); setSearch($("#form1").val())}} className="btn btn-primary">
              <i className="fa fa-search"></i>
            </button>
          </div>
      </div>
      <table className="table table-striped">
        <thead className="thead-dark">
          <tr>
            <th scope="col">#</th>
            <th scope="col">Title</th>
            <th scope="col">Description</th>
            <th scope="col">FileName</th>
            <th scope="col">UUID</th>
            <th scope="col"></th>
          </tr>
        </thead>
        <tbody >
          {packages.map(i => (
            <tr className={i.uuid === selected ? "table-primary" : ""}>
              <th scope="row">{i.id}</th>
              <td>{i.title}</td>
              <td>{i.description}</td>
              <td>{i.filename}</td>
              <td>{i.uuid}</td>
              <td>
                <div class="dropdown">
                  <button
                    class="btn btn-secondary dropdown-toggle"
                    type="button"
                    id={"dropdownMenuButton" + i.id}
                    data-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                  >
                    Actions
                  </button>
                  <div
                    class="dropdown-menu"
                    aria-labelledby={"dropdownMenuButton" + i.id}
                  >
                    <a class="dropdown-item" onClick={() => deleteCourse(i)}>
                      Delete
                    </a>
                 
                    <a
                      onClick={() => {
                        setSelected(i.uuid);
                        $("#id_introeditoreditable").text(i.description);
                        $("#id_name").val(i.title);
                        $("#id_introeditoreditable").focus();
                      }}
                      class="dropdown-item"
                    >
                      Select
                    </a>
                  </div>
                </div>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
      <Pages currentPage={page} onPage={setPage} total={total} perPage={10}></Pages>
    </div>
  );
}

function ShowSelected({selected,API})
{
  let [selectedPackage,setSelectedPackage] = useState();
  useEffect( ()=>{
    API.ajax({
      url: "/mod/scormengine/service/getPackage.php?id="+ selected,
      method: "GET",
      success: (body, status, xhr) => {
        
        if(body && xhr.status == 200)
        {
          setSelectedPackage(body);
        }
      }
    });

  },[selected])
  if(selectedPackage)
  {
    return <div className="alert alert-primary" style={{margin:"1em"}}>
      <h3><b>Current Selection:</b> {selectedPackage.title}</h3>
      <p>{selectedPackage.description}</p>
      <p>{selectedPackage.filename}</p>
    </div>
  }
  else 
  return "";
}
let _errors = [];
$("#id_package_id").hide();
let onForm = $("#id_package_id").length > 0;
function Main() {
  let [mode, setMode] = useState(window.location.hash || "list");
  let [navEnabled, setNavEnabled] = useState(true);
  const [errors, setError] = useState([]);
  let [selected, setSelected] = useState($("#id_package_id").val());

  useEffect(()=>{
    window.location.hash = mode;
  },[mode])
  useEffect(()=>{
    $(window).on('hashchange',(e)=>{
     let newUrl = new URL(e.originalEvent.newURL);
     if(newUrl.hash) 
     setMode(newUrl.hash);
    })
  },[mode])
  

  
  let saveLog = function () {
    var data = new Blob([_errors.map(i=>i.err).join("\n\n")], {type: 'text/plain'});
    // If we are replacing a previously generated file we need to
    // manually revoke the object URL to avoid memory leaks.
    

    let textFile = window.URL.createObjectURL(data);

    var link = document.createElement('a');
    link.setAttribute('download', 'log.txt');
    link.href = textFile;
    document.body.appendChild(link);

    // wait for the link to be added to the document
    window.requestAnimationFrame(function () {
      var event = new MouseEvent('click');
      link.dispatchEvent(event);
      document.body.removeChild(link);
    });

  };

  function addError(err)
  {
    //errors.push({err:err,key:err})
    _errors.push({err:err,key:err});
    let e = errors.concat([{err:err,key:err}]);
    console.log(e);
    setError(e);
  }
  let API = {
    addError:function(err)
    {
      addError( err);
    },
    ajax: function({
      name,
      url,
      method,
      success,
      processData,
      contentType,
      type,
      data,
      xhr,
      error
    }) {
      $.ajax({
        url : window.site_home + url,
        method,
        success,
        processData,
        contentType,
        type,
        data,
        xhr,
        success: (body, status, xhr) => {
          console.log(status);
          success(body, status, xhr);
        },
        error: (xhr, body, status) => {
          if (xhr.status == 401) {
            addError("Unauthorized, please log in as an administrator");
          } else {
            if (xhr.status == 400 && xhr.responseJSON.error == "Package Error")
            addError(
                name + ": " + "Package Error" +
                  ": " +
                  xhr.responseJSON.data.parserWarnings.join(", ")
              );
            else {
              addError("An unknown error occurred");
            }
          }
          if(error)
            error(xhr,body,status)
        }
      });
    }
  };

  useEffect(() => {
    $("#id_package_id").val(selected);
  }, [selected]);
let _mode = mode.replace("#","")
  return (
    <div class="container">
      {
        selected && <ShowSelected selected={selected} API={API}></ShowSelected>
      }

      {errors.length > 0 && _errors.map( error => 
        <div key={error.key} className="alert alert-danger">{error.err}</div>,
      )}
      {errors.length > 0 && <div className="btn btn-danger btn-raised" onClick={() => {setError([]); _errors=[];}}>
          Dismiss
        </div>
        }
      {errors.length > 0 && <div className="btn btn-danger btn-raised" onClick={() => {saveLog()}}>
          Save Log
        </div>
        }
      {errors.length ==0  && navEnabled &&(
        <ul className="nav nav-tabs">
          <li className="nav-item">
            <a
              className={"nav-link " + (_mode === "list" ? "active" : "")}
              onClick={() => setMode("list")}
              href="#"
            >
              List
            </a>
          </li>
   
          <li className="nav-item">
            <a
              className={"nav-link " + (_mode === "bulk" ? "active" : "")}
              onClick={() => setMode("bulk")}
              href="#"
            >
              Upload
            </a>
          </li>
          {selected && (
            <li className="nav-item">
              <a
                className={"nav-link " + (_mode === "attempts" ? "active" : "")}
                onClick={() => setMode("attempts")}
                href="#"
              >
                Attempts
              </a>
            </li>
          )}
        </ul>
      )}

      <div style={{borderBottom:"1.5px solid #e5e5e5",borderRight:"1.5px solid #e5e5e5",borderLeft:"1.5px solid #e5e5e5", padding:"10px"}}>      
      {errors.length ==0 && _mode == "upload" && (
        <Upload API={API} onUpload={() => setMode("list")}></Upload>
      )}
      {errors.length ==0 && _mode == "list" && (
        <List API={API} selected={selected} setSelected={setSelected}></List>
      )}
      {_mode == "bulk" && (
        <Bulk API={API} onUpload={() => setMode("list")} setNavEnabled={setNavEnabled}></Bulk>
      )}
      {errors.length ==0 && _mode == "attempts" && (
        <Attempts API={API} selected={selected}></Attempts>
      )}
      </div>
    </div>
  );
}

function Bulk({ onUpload, API,setNavEnabled }) {
  const [selectedFiles, setSelectedFiles] = useState([]);
  const [uploadProgress, setUploadProgress] = useState(0);
  let [bulkProgress, setBulkProgress] = useState(0);
  const [state, setState] = useState("");

  async function doBulk(e) {
    e.preventDefault();
    setNavEnabled(false)
    for (let i of selectedFiles) {
      await upload(i);
      bulkProgress += 1
      setBulkProgress(bulkProgress);
    }
    setNavEnabled(true)
    onUpload();
  }
  function upload(selectedFile) {
    
    return new Promise(res => {
      const formData = new FormData();
      formData.append("package", selectedFile);
     // formData.append("title", selectedFile.name);
     // formData.append("description", "Uploaded by bulk upload tool.");
      setState("uploading");
      API.ajax({
        url: "/mod/scormengine/service/uploadPackage.php",
        method: "POST",
        name: selectedFile.name,
        processData: false,
        contentType: false,
        type: "POST",
        data: formData,
        success: (body, status, xhr) => {
          setUploadProgress(0)
          res();
        },
        error: () =>
        {
          setUploadProgress(0)
           res()
        },
        xhr: function() {
          var xhr = new window.XMLHttpRequest();
          xhr.upload.addEventListener(
            "progress",
            function(evt) {
              if (evt.lengthComputable) {
                var percentComplete = (evt.loaded / evt.total) * 100;
                console.log(percentComplete);
                setUploadProgress(percentComplete);
              }
            },
            false
          );
          xhr.upload.addEventListener(
            "load",
            function(evt) {
              setState("processing");
            },
            false
          );
          return xhr;
        }
      });

      return false;
    });
  }
  return (
    <form
      onSubmit={e => doBulk(e)}
    >
      {selectedFiles.length > 0 && (
        <ul>
            {selectedFiles.map((s,i) => <li style={{fontWeight: i == bulkProgress ? "bold":"", opacity: i < bulkProgress ? ".1":""} }>{s.name}</li>)}
        </ul>
      )}

      <div className="form-group">
        <label for="package">Package file</label>
        <input
          required
          onChange={e => {
            setSelectedFiles(Array.from(e.target.files));
          }}
          type="file"
          className="form-control"
          id="package"
          name="package"
          aria-describedby="packageHelp"
          placeholder="Choose a .zip file"
          multiple={true}
          accept="*.zip"
        ></input>
        <small id="packageHelp" className="form-text text-muted">
          Choose a .zip file that contains the course content in SCORM format.
        </small>
      </div>

      {state == "" && (
        <button type="submit" className="btn btn-primary">
          Upload
        </button>
      )}
      {state && (
        <p>
          <div className="fa fa-spin fa-spinner"></div> {state}
        </p>
      )}
      {state == "uploading" && (
        <div class="progress">
          <div
            class="progress-bar"
            role="progressbar"
            style={{ width: uploadProgress + "%" }}
            aria-valuenow={uploadProgress}
            aria-valuemin="0"
            aria-valuemax="100"
          >
            {uploadProgress.toFixed(2) + "%"}
          </div>
        </div>
      )}
      {selectedFiles.length > 0 && state != "" && (
        <div class="progress">
          <div
            class="progress-bar"
            role="progressbar"
            style={{ width: (bulkProgress / selectedFiles.length) * 100 + "%" }}
            aria-valuemin="0"
            aria-valuemax="100"
          >
            {bulkProgress + " of " + selectedFiles.length + " files"}
          </div>
        </div>
      )}
    </form>
  );
}

function Upload({ onUpload, API }) {
  const [title, setTitle] = useState("");
  const [description, setDescription] = useState("");
  const [selectedFile, setSelectedFile] = useState(null);
  const [uploadProgress, setUploadProgress] = useState(0);
  const [state, setState] = useState("");

  function upload(e) {
    const formData = new FormData();
    formData.append("package", selectedFile);
    formData.append("title", title);
    formData.append("description", description);
    setState("uploading");
    API.ajax({
      url: "/mod/scormengine/service/uploadPackage.php",
      method: "POST",
      processData: false,
      contentType: false,
      type: "POST",
      data: formData,
      success: (body, status, xhr) => {
        setState("");
        if (onUpload) onUpload();
      },
      xhr: function() {
        var xhr = new window.XMLHttpRequest();
        xhr.upload.addEventListener(
          "progress",
          function(evt) {
            if (evt.lengthComputable) {
              var percentComplete = (evt.loaded / evt.total) * 100;
              console.log(percentComplete);
              setUploadProgress(percentComplete);
            }
          },
          false
        );
        xhr.upload.addEventListener(
          "load",
          function(evt) {
            setState("processing");
          },
          false
        );
        return xhr;
      }
    });
    e.preventDefault();
    return false;
  }
  return (
    <form
      onSubmit={e => upload(e)}
      action="/mod/scormengine/service/uploadPackage.php"
      method="POST"
      encType="multipart/form-data"
    >
      <div className="form-group">
        <label for="package">Package file</label>
        <input
          required
          onChange={e => {
            setSelectedFile(e.target.files[0]);
            setTitle(e.target.files[0].name);
          }}
          type="file"
          className="form-control"
          id="package"
          name="package"
          aria-describedby="packageHelp"
          placeholder="Choose a .zip file"
        ></input>
        <small id="packageHelp" className="form-text text-muted">
          Choose a .zip file that contains the course content in SCORM format.
        </small>
      </div>
      <div className="form-group">
        <label for="tilte">Title</label>
        <input
          onChange={e => setTitle(e.target.value)}
          value={title}
          type="text"
          className="form-control"
          id="title"
          placeholder="Package Title"
          required
        ></input>
        <small id="packageHelp" className="form-text text-muted">
          This is a title for the package file. You can name the Moodle course
          activity that uses it separately.
        </small>
      </div>
      <div className="form-group">
        <label for="tilte">Description</label>
        <textArea
          onChange={e => setDescription(e.target.value)}
          type="text"
          className="form-control"
          id="title"
          placeholder="Package Title"
        ></textArea>
        <small id="packageHelp" className="form-text text-muted">
          A space for keeping notes about this content. The learner will never
          see this text.
        </small>
      </div>
      {state == "" && (
        <button type="submit" className="btn btn-primary">
          Upload
        </button>
      )}
      {state && (
        <p>
          <div className="fa fa-spin fa-spinner"></div> {state}
        </p>
      )}
      {state == "uploading" && (
        <div class="progress">
          <div
            class="progress-bar"
            role="progressbar"
            style={{ width: uploadProgress + "%" }}
            aria-valuenow={uploadProgress}
            aria-valuemin="0"
            aria-valuemax="100"
          >
            {uploadProgress.toFixed(2) + "%"}
          </div>
        </div>
      )}
    </form>
  );
}

export default Main;
