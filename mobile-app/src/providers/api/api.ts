import { HttpClient, HttpParams, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { URLSearchParams } from '@angular/http';
import { TranslateService } from '@ngx-translate/core';
import { Storage } from '@ionic/storage';

@Injectable()
export class Api 
{
  _lang: any = null;
  /**
   * Ubah variable URI API ke dalam proxy jika mengetes di browser dan Deploy IOS
   * File Configurasi proxy ada di ionic.config.json
   * LIST IP ADDRESS DEVELOPE:
   * [Offline] Router IndiHome = http://192.168.100.155:333
   * [Offline] Router TP-LINK Portable = http://192.168.0.100:333
   * [Online] = https://bumdesapp.com
   */
  urlAPI: string = 'http://localhost:333';
  urlPing: string = this.urlAPI + '/api2/ping';
  urlGetRequest: string = this.urlAPI + '/api2/v1';
  urlPostAjax: string = this.urlAPI + '/api2/v2';
  urlPostNode: string = this.urlAPI + '/api2/v3';
  urlPostUpload: string = this.urlAPI + '/api2/v4';

  constructor(
    private translate: TranslateService,
    private storage: Storage, 
    public http: HttpClient) 
  {
    this.getLang()
  }

  getLang()
  {
    this.storage.get('_settings')
    .then((settings) => {
      //console.log(settings)
      this._lang = settings ? settings.opt_lang : this.translate.getBrowserLang();
    });
  }

  ping() 
  {
    this.getLang();
    let reqOpts = {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      params: new HttpParams()
    };

    return this.http.get(this.urlPing, reqOpts);
  }

  get(endpoint: string, params?: any, reqOpts?: any) 
  {
    endpoint = this._lang == null ? endpoint : endpoint + '?lang=' + this._lang;
    if(!reqOpts) 
    {
      reqOpts = {
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        params: new HttpParams()
      };
    }

    if(params) 
    {
      reqOpts.headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      };

      reqOpts.params = new HttpParams();
      for (let k in params) 
      {
        reqOpts.params = reqOpts.params.set(k, params[k]);
      }
    }

    return this.http.get(this.urlGetRequest + '/' + endpoint, reqOpts);
  }

  post(endpoint: string, body: any, reqOpts?: any) 
  {
    endpoint = this._lang == null ? endpoint : endpoint + '?lang=' + this._lang;
    reqOpts = reqOpts ? reqOpts : {
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
      },
      params: new HttpParams()
    };

    let paramsEncode = new URLSearchParams();
    let reformat = {init: body.init, pack: JSON.stringify(body.pack)};
    for(let key in reformat)
    {
       paramsEncode.set(key, reformat[key]) 
    }

    return this.http.post(this.urlPostNode + '/' + endpoint, paramsEncode.toString(), reqOpts);
  }

  upload(endpoint: string, body: any) 
  {
    endpoint = this._lang == null ? endpoint : endpoint + '?lang=' + this._lang;
    let reqOpts = {
      headers: new HttpHeaders(
        {
          'Content-Type' : 'application/octet-stream',
          'X-Requested-With': 'XMLHttpRequest'
        }
      )
    }

    return this.http.post(this.urlPostUpload + '/' + endpoint, JSON.stringify(body), reqOpts);
  }

  put(endpoint: string, body: any, reqOpts?: any) 
  {
    endpoint = this._lang == null ? endpoint : endpoint + '?lang=' + this._lang;
    return this.http.put(this.urlPostNode + '/' + endpoint, body, reqOpts);
  }

  delete(endpoint: string, reqOpts?: any) 
  {
    endpoint = this._lang == null ? endpoint : endpoint + '?lang=' + this._lang;
    return this.http.delete(this.urlPostNode + '/' + endpoint, reqOpts);
  }

  patch(endpoint: string, body: any, reqOpts?: any) 
  {
    endpoint = this._lang == null ? endpoint : endpoint + '?lang=' + this._lang;
    return this.http.patch(this.urlPostNode + '/' + endpoint, body, reqOpts);
  }
}
