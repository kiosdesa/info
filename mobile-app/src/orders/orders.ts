import { Component } from '@angular/core';
import { DomSanitizer } from '@angular/platform-browser';
import { TranslateService } from '@ngx-translate/core';
import { IonicPage, ToastController, NavParams, Events, NavController, AlertController } from 'ionic-angular';
import { RequestApiProvider, Online } from '../../providers/providers';

@IonicPage()
@Component({
  selector: 'page-order',
  templateUrl: 'orders.html',
})
export class OrderPage 
{
  _dataOrder: any;
  _dataProduct: any;
  _total: number = 0;
  _symbolKurs: string;
  _cdnIcon: string;
  _cdnProduct: string;
  _paramRoots: any = {};

  _groupOrder: boolean = false;
  _showBlank: boolean = false;
  _isSeller:boolean = false;
  _tabLoop: any;
  tabReceive: string;

  private cancelButton: string;
  private confirmButton: string;
  private complainButton: string;
  private atemptTitle: string;
  private atemptMessage: string;
  private refundMessage: string;
  private doneButton: string;
  private buyerText: string;

  constructor(
    private api: RequestApiProvider, 
    public sanitizer: DomSanitizer, 
    private online: Online, 
    public event: Events, 
    public translate: TranslateService, 
    public navCtrl: NavController, 
    public toast: ToastController,
    public alert: AlertController, 
    public params: NavParams) 
  {
    this._paramRoots = this.params.get('paramRoots')
    this._isSeller = 'seller' in this._paramRoots ? this._paramRoots.seller : false;
    const _defaultVerifSegment = [1]
    const _sellerVerifSegment = this._isSeller == true ? [2] : _defaultVerifSegment
    
    this.translate.get([
      'ATTENTION', 'CONFIRM_MESSAGE', 'CLOSE_BUTTON', 'CONFIRM_TITLE', 'COMPLAIN_BUTTON', 'USER_BALANCE_REFUND', 'DONE_BUTTON', 'BUYER',
      'ORDER_VERIF_PAY','PROCESS_BUTTON','DONE_BUTTON','DENY_BUTTON',
    ])
    .subscribe((value)=>{
		  this.cancelButton = value.CLOSE_BUTTON;
		  this.confirmButton = value.CONFIRM_TITLE;
      this.complainButton = value.COMPLAIN_BUTTON;
      this.atemptTitle = value.ATTENTION;
      this.atemptMessage = value.CONFIRM_MESSAGE;
      this.refundMessage = value.USER_BALANCE_REFUND;
      this.doneButton = value.DONE_BUTTON;
      this.buyerText = value.BUYER;

      this._tabLoop = [
        {name:value.ORDER_VERIF_PAY, segment:'verify', shipping:_sellerVerifSegment, receive:0, group:true},
        {name:value.PROCESS_BUTTON, segment:'process', shipping:[2,3,4,5,6,8], receive:0, group:false},
        {name:value.DONE_BUTTON, segment:'done', shipping:[7], receive:1, group:false},
        {name:value.DENY_BUTTON, segment:'deny', shipping:[9,10], receive:undefined, group:false}
      ]
    })

    this.event.subscribe('switch:clicked',(data)=>{
      this.tabReceive = data.segment;
      this.loadOrder(data.status, data.shipping)
    })
  }

  ionViewDidLoad()
  {
    this.tabReceive = (("segment" in this._paramRoots) ? this._paramRoots['segment'] : 'process')
    const receiveParam = (("receive" in this._paramRoots) ? this._paramRoots['receive'] : 0)
    const shippingParam = (("shipping" in this._paramRoots) ? this._paramRoots['shipping'] : [2,3,4,5,6,8])
    const groupParam = (("group" in this._paramRoots) ? this._paramRoots['group'] : undefined)
    this.loadOrder(receiveParam, shippingParam, groupParam)
  }

  async loadOrder(status?:any, shipping?:any, group?:boolean)
  {
    this._dataProduct = [];
    group = group ? group : false;
    const endpoint = 'listsOrders';
    let field = {};

    if(status) field['status_receive'] = status;
    if(shipping) field['status_shipping'] = shipping;
    if(group) field['group'] = group;
    if('seller' in this._paramRoots) field['seller'] = this._paramRoots.seller;

    await this.api.post('transaction', endpoint, {init:'orders-lists', pack:{fieldForm:field}})
    .subscribe((res:any)=>{
      const Resp = res[endpoint];
      const theItems = Resp.items;
      if(theItems.approve == true)
      {
        this._showBlank = false;
        this._groupOrder = group;
        this._dataOrder = theItems.data;
        this._total = theItems.total;
        this._symbolKurs = theItems.symbol;
        this._cdnIcon = theItems.server.icon;
        this._cdnProduct = theItems.server.product;
      }
      else
      {
        this._showBlank = true;
      }
    },(err)=>{
      this._showBlank = true;
      this.online.checkOnline(true)
    })
  }

  async confirmUpdate(p?:any, message?:any)
  {
    const endpoint = 'updateOrders';
    const field = p ? p : {};
    await this.api.post('transaction', endpoint, {init:'orders-update', pack:{fieldForm:field}})
    .subscribe((res:any)=>{
      const Resp = res[endpoint];
      const toastMessage = message ? message : Resp.items.message;
      let toast = this.toast.create({
        message: toastMessage,
        duration: 5000,
        position: 'top'
      });
      toast.present();
    },
    (err)=>{
      this.online.checkOnline(true)
    })
  }

  confirmPay(p)
  {
		const _paramRoots = ("param" in p) ? p.param : {};
		this.navCtrl.push(p.page, {paramRoots:_paramRoots}, {animate:true});
  }

  detail(index)
  {
    //console.log(index)
    if(this._dataProduct.length > 0 && index in this._dataProduct) this._dataProduct = [];
    this._dataProduct[index] = this._dataOrder[index].data_product;
  }

  cancelOrder(param, index)
  {
    let alert = this.alert.create({
      cssClass: 'no-scroll',
      title: this.atemptTitle,
      message: this.atemptMessage,
      buttons: [{
				text: this.confirmButton,
        handler:()=>{
          const _message = this._dataOrder[index].total_payment_format + ' ' + this.refundMessage;
          this.confirmUpdate({invoice:param, status_shipping:10, clause:true}, _message).then(()=>{
            this.changeStatusOrderSegment(index)
          })
        }
      },
      {
				text: this.cancelButton,
				role: 'cancel'
      }]
    });
    alert.present()
  }

  responseDeliver(param, index, update?:boolean)
  {
    const _cancelHandler = {
      text: this.cancelButton,
      role: 'cancel'
    }

    const _complaintHandler = {
      text: this.complainButton,
      handler:()=>{
        this.navCtrl.push('OrderComplaintPage', {
          paramRoots:{invoice:param, message:this._dataOrder[index].note}
        }, {animate:true});
      }
    }

    const _approveHandler = {
      text: this.confirmButton,
      handler:()=>{
        this.navCtrl.push('OrderResponsePage', {paramRoots:param}, {animate:true})
        .then(()=>{
          if(update == true)
          {
            const _message = this._dataOrder[index].note ? {message:this._dataOrder[index].note + ',<hr style=\'color:#eef\'>' + this.buyerText + ': <br>' + this.doneButton + ' [' + this.getDateNow() + '] '} : {};
            this.confirmUpdate({invoice:param, status_receive:1, status_shipping:7, ..._message}).then(()=>{
              this.changeStatusOrderSegment(index)
            })
          }
        })
      }
    }

    const _uxHandler = update == true ? [_cancelHandler, _approveHandler, _complaintHandler] : [_cancelHandler, _approveHandler];

    let alert = this.alert.create({
      cssClass: 'no-scroll',
      title: this.atemptTitle,
      message: this.atemptMessage,
      buttons: _uxHandler
    });
    alert.present();
  }

  changeStatusOrderSegment(index)
  {
    if(index > -1) this._dataOrder.splice(index, 1)
    this._dataProduct = []
  }

  getDateNow()
  {
    let dateFormat = require('dateformat');
    let now = new Date();
    return dateFormat(now, "dd/mm/yyyy, h:MM:ss TT");
  }
}