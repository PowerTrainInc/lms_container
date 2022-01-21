
import React, { Component, useState, useEffect } from "react";
import settingCategories from "./settingsCategories.json";

function renderSetting(setting,setSettings,filter)
{
    if(filter)
    {
        let _filter = new RegExp(filter);
        if(!setting.id.match(_filter) && !setting.metadata.settingDescription.match(filter) )
        {
            return;
        }
    }
    function setSetting(setting,value)
    {
        if(setting.initialValue === undefined)
            setting.initialValue = setting.effectiveValue;
        setting.effectiveValue=value;
        
            setting.dirty = (setting.effectiveValue != setting.initialValue);
        setSettings()
    }
    if(setting.metadata.level == "Registration")
    {
        
        if(setting.metadata.dataType === "string" || setting.metadata.dataType === "delimitedString")
            return <div className={"form-group"} style={{background:(setting.dirty ? " #ffdfdf": "")}}>
                <div><label for="package">{setting.id}</label></div>
                <input
            
                
                onChange={e => {
                    setSetting(setting,e.target.value);
                }}
                value={setting.effectiveValue}
                >
                </input>
                <small id="packageHelp" className="form-text text-muted">
                {setting.metadata.settingDescription}
                </small>
            </div>
         if( setting.metadata.dataType === "secretString")
         return <div className="form-group" style={{background:(setting.dirty ? " #ffdfdf": "")}}>
             <div><label for="package">{setting.id}</label></div>
             <input
           
            type="password"
             onChange={e => {
                setSetting(setting,e.target.value);
             }}
             value={setting.effectiveValue}
             >
             </input>
             <small id="packageHelp" className="form-text text-muted">
             {setting.metadata.settingDescription}
             </small>
         </div>
        if(setting.metadata.dataType === "bool")
        return <div className="form-group" style={{background:(setting.dirty ? " #ffdfdf": "")}}>
             <label for="package" style={{paddingRight:"1em"}}>{setting.id}</label>
            <input
         
            type="checkbox"
            onChange={e => {
                setSetting(setting,e.target.checked.toString());
            }}
            checked={setting.effectiveValue=="true"}
            >
            </input>
            <small id="packageHelp" className="form-text text-muted">
            {setting.metadata.settingDescription}
            </small>
        </div>
        if(setting.metadata.dataType.match(/^Enum/))
        return <div className="form-group" style={{background:(setting.dirty ? " #ffdfdf": "")}}>
            <div><label for="package">{setting.id}</label></div>
            <select
              className='fake-class'
              onChange={e => {
                  setSetting(setting,e.target.value);
              }}
              style={{width: '100%'}}
            >
                {setting.metadata.validValues && setting.metadata.validValues.map(val=><option selected={setting.effectiveValue == val.value} value={val.value}>{val.valueDescription}</option>)}
            
            </select>
            <small id="packageHelp" className="form-text text-muted">
            {setting.metadata.settingDescription}
            </small>
        </div>

        if(setting.metadata.dataType === 'long' || setting.metadata.dataType === 'int' || setting.metadata.dataType === 'nonNegativeInt' || setting.metadata.dataType === 'positiveInt' )
        return <div className="form-group" style={{background:(setting.dirty ? " #ffdfdf": "")}}>
            <div><label for="package">{setting.id}</label></div>
            <input
            

            onChange={e => {
                setSetting(setting,e.target.value);
            }}
              value={setting.effectiveValue}
            >
              
            </input>
            <small id="packageHelp" className="form-text text-muted">
            {setting.metadata.settingDescription}
            </small>
        </div>
        if(setting.metadata.dataType === 'mergedJson' || setting.metadata.dataType === 'secretMergedJson')
        return <div className="form-group" style={{background:(setting.dirty ? " #ffdfdf": "")}}>
            <div><label for="package">{setting.id}</label></div>
            <textarea
            

            onChange={e => {
                setSetting(setting,e.target.value);
            }}
            >
              {setting.effectiveValue}
            </textarea>
            <small id="packageHelp" className="form-text text-muted">
            {setting.metadata.settingDescription}
            </small>
        </div>
        
    }
}

export default function Settings({ selected, API }) {
   
    const [settings, setSettings] = useState({});
    const [status, setStatus] = useState("clean");
    const [filter, setFilter] = useState();
    const [selectedCategory, setSelectedCategory] = useState(settingCategories[0].category);
    const [buttonHover, setButtonHover] = useState(false);

    function download(){
        var text = JSON.stringify(settings,null,4)
        var a = window.document.createElement('a');
        a.href = window.URL.createObjectURL(new Blob([text], {type: 'application/json'}));
        a.download = 'settings.json';
        a.target="_blank";
    
        document.body.appendChild(a)
        a.click();
        document.body.removeChild(a)
    }

    function readSingleFile(e) {
        var file = e.target.files[0];
        if (!file) {
          return;
        }
        var reader = new FileReader();
        reader.onload = function(e) {
          var contents = e.target.result;
          let loadedSettings = JSON.parse(contents);
          for(let i in loadedSettings.settingItems)
          {
            for(let j in settings.settingItems)
            {
                if(settings.settingItems[j].id == loadedSettings.settingItems[i].id)
                {
                    if(settings.settingItems[j].effectiveValue != loadedSettings.settingItems[i].effectiveValue)
                    {
                        settings.settingItems[j].effectiveValue = loadedSettings.settingItems[i].effectiveValue
                        settings.settingItems[j].dirty=true;
                    }
                    break;
                }
            }
          }
          setSettings(JSON.parse(JSON.stringify(settings)))
        };
        reader.readAsText(file);
      }
      
      function displayContents(contents) {
        var element = document.getElementById('file-content');
        element.textContent = contents;
      }
      
     

    useEffect(()=>{
    
        API.ajax({
            url: "/mod/scormengine/service/getSettings.php?id="+selected,
            method: "GET",
            success: (body, status, xhr) => {
                //body.settingItems = body.settingItems.filter(i=>i.id == "ConcurrentLaunchDetectionHistoryRows")
              setSettings(body);
            },
          });

    },[selected])
    function uploadSettings(e) {
      
      
      let updatedSettings = settings.settingItems.filter(i=>i.dirty).map(s=>{return {settingId:s.id,value:s.effectiveValue}})

      API.ajax({
        url: "/mod/scormengine/service/setSettings.php?id="+selected,
        method: "POST",
        processData: false,
        contentType: "application/json",
        data: JSON.stringify({settings:updatedSettings}),
        success: (body, status, xhr) => {
            settings.settingItems.forEach(s=>s.dirty = false);
            setSettings(JSON.parse(JSON.stringify(settings)))
        },
      });
      e.preventDefault();
      return false;
    }

    const labelBtn = {
        display: 'inlineBlock',
        textAlign: 'center',
        fontSize: '.9375rem',
        fontWeight: '400',
        verticalAlign: 'middle',
        lineHeight: '1.5',
        padding: '.375rem .75rem',
        marginTop: '.5rem',
        backgroundColor: buttonHover ? '#0c5ba1' : '#0f6fc5',
        color: '#FFFFFF',
        border: '1px solid transparent',
        cursor: 'pointer',
    };

    return (
      <form
      
        method="POST"
      >
        {/* <label for="package" style = {{marginRight:"1em"}}>Search Settings</label>
        <input onChange={e=>setFilter(e.target.value)} ></input> */}
        <div className="form-group">
          <textarea
           
            style = {{display:"none", width:"100%", height:"400px"}}
           
            onChange={e => {
              setSettings(JSON.stringify(e.value));
              setStatus("dirty");
            }}
            value={JSON.stringify(settings,null,4)}
          >
              
          </textarea>
        </div>

        <ul className="nav nav-tabs" style={{marginBottom: '1rem'}}>
          {
            settingCategories.map((sc, key) => (<>
              <li className="nav-item" key={`${sc}.${key}`}>
                <a
                  className={`nav-link ${(selectedCategory === sc.category ? "active" : "")}`}
                  style={{fontSize: '12px'}}
                  onClick={() => setSelectedCategory(sc.category)}
                  href="javascript:void(0)"
                >
                  {sc.category}
                </a>
              </li>
            </>))
          }
        </ul>

        {
            settings.settingItems && (<>
              {
                settingCategories.filter(sc => sc.category === selectedCategory).map(sc => (<>
                    { 
                        sc.settings.map(scs => (<>
                          {
                            settings.settingItems.filter(s => s.id === scs).map(i => (renderSetting(i, () => setSettings(JSON.parse(JSON.stringify(settings))),filter)))
                          }
                        </>))
                    }
                </>))
              }
            </>)
        }
        
        <button class="btn btn-primary" style={{marginRight: '.25rem'}} type="" onClick={e => uploadSettings(e)}>Submit</button>
        <button class="btn btn-primary" style={{marginRight: '.25rem'}} onClick={()=>download()}>Export</button>
        <label style={labelBtn} for="file-upload" class="custom-file-upload" onMouseEnter={() => setButtonHover(true)} onMouseLeave={() => setButtonHover(false)}>
              Import
          </label>
        <input style={{display:"none"}} id="file-upload" type="file" onChange={readSingleFile}></input>
      </form>
    );
  }
  