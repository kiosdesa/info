import 'rxjs/add/operator/toPromise';
import { ToastController } from 'ionic-angular';
import { Injectable } from '@angular/core';
import { Api } from '../api/api';

@Injectable()
export class RequestApiProvider 
{
  constructor(
    private api: Api, 
    public toastCtrl: ToastController)
  {}

	get(root?:any, endpoint?:any, param?:any)
	{
    let seq = this.api.get(root, param).share();
    seq.subscribe((res: any) => {
      let resp = res[endpoint];
      if(resp.status.code !== 200) 
      {
        let toast = this.toastCtrl.create({
          message: resp.items.message,
          duration: 3000,
          position: 'top'
        });
        toast.present();
      }
    }, (err) => {});
    return seq;
	}


	post(root?:any, endpoint?:any, param?:any, opt?:any)
	{
		let seq = this.api.post(root, param, opt).share();
		seq.subscribe((res:any) => {
      let resp = res[endpoint];
      if(resp.status.code !== 200) 
      {
        let toast = this.toastCtrl.create({
          message: resp.items.message,
          duration: 3000,
          position: 'top'
        });
        toast.present();
      }
		}, 
		(err) => {});
		return seq;
	}
}
