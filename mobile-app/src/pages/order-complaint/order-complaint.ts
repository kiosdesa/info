import { Component } from '@angular/core';
import { DomSanitizer } from '@angular/platform-browser';
import { TranslateService } from '@ngx-translate/core';
import { IonicPage, NavController, Events, NavParams, ToastController } from 'ionic-angular';
import { RequestApiProvider } from '../../providers/providers';

export interface formComplaint
{
  invoice: any
  message: string
}

@IonicPage()
@Component({
  selector: 'page-order-complaint',
  templateUrl: 'order-complaint.html',
})
export class OrderComplaintPage
{
  _complaintData: formComplaint;
  _paramRoots: any;

  _tempMessage: string = undefined;
  private errorMessage: string;
  private emptyMessage: string;
  private buyerText: string;

  constructor(
    private api: RequestApiProvider, 
    public sanitizer: DomSanitizer, 
    public event: Events, 
    public translate: TranslateService, 
    public toast: ToastController, 
    public navCtrl: NavController, 
    public navParams: NavParams)
  {
    this._paramRoots = this.navParams.get('paramRoots');
    this._complaintData = {
      invoice: this._paramRoots.invoice, message:undefined
    }

    this.translate.get(['EMPTY_MESSAGE', 'ERROR', 'BUYER'])
    .subscribe((value)=>{
		  this.emptyMessage = value.EMPTY;
      this.errorMessage = value.ERROR;
      this.buyerText = value.BUYER;
    })
  }

  ionViewDidEnter()
  {
    this._tempMessage = this._paramRoots.message;
  }
  
  sendComplaint()
  {
    if(this._complaintData.message == undefined)
    {
      this.toasting(this.emptyMessage)
    }
    else
    {
      if(this._tempMessage)
      {
        this._complaintData.message = this._tempMessage + ',<hr style=\'color:#eef\'>' + this.buyerText + ': <br>' + this._complaintData.message + ' [' + this.getDateNow() + '] '
      }
      else
      {
        this._complaintData.message = this.buyerText + ': <br>' + this._complaintData.message + ' [' + this.getDateNow() + '] '
      }

      const fields = {...this._complaintData, status_shipping: 8};
      const endpoint = 'updateOrders';
      this.api.post('transaction', endpoint, {init:'orders-update', pack:{fieldForm:fields}})
      .subscribe((res:any)=>{
        const Resp = res[endpoint];
        this.toasting(Resp.items.message, 'success-toast')
        this.navCtrl.pop().then(()=>{
          this.event.publish('switch:clicked',{segment:'process', status:0, shipping:[2,3,4,5,6,8]});
        })
      },
      (err)=>{
        this.toasting(this.errorMessage)
      })
      //console.log(this._complaintData)
    }
  }

  toasting(param, color?:any)
  {
    const colors = color ? color : 'danger-toast';
    let toast = this.toast.create({
      message: param,
      cssClass: colors,
      duration: 5000,
      position: 'top'
    });
    toast.present();
  }

  getDateNow()
  {
    let dateFormat = require('dateformat');
    let now = new Date();
    return dateFormat(now, "dd/mm/yyyy, h:MM:ss TT");
  }
}