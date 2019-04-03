import { Component } from '@angular/core';
import { DomSanitizer } from '@angular/platform-browser';
import { TranslateService } from '@ngx-translate/core';
import { SeparateNumberPipe } from './../../pipe/separate-number';
import { IonicPage, NavController, NavParams, Events, LoadingController, AlertController, ModalController } from 'ionic-angular';
import { RequestApiProvider, NotifyProvider, Online } from '../../providers/providers'

@IonicPage()
@Component({
  selector: 'page-cart-pay',
  templateUrl: 'cart-pay.html',
  providers: [SeparateNumberPipe]
})
export class CartPayPage 
{
  _dataParam: any;
  _dataPayList: any;
  _dataChoosePay: any;
  _dataBalance: any;
  _messageBalance: any;
  _totalToPay: any;
  _currencyFormat: string;
  _cdnIcon: string;

  showBlank: boolean;
  payNowButtonStatus: boolean;

  PayChooseRequire: string;
  ErrorText: string;
  ErrorTitle: string;
  OKButton: string;

  constructor(
    private online: Online, 
    private reqapiprov: RequestApiProvider, 
    private notif: NotifyProvider, 
    public sanitizer: DomSanitizer, 
    private seNum: SeparateNumberPipe, 
    public navParams: NavParams, 
    public translate: TranslateService, 
    public events: Events, 
    public loading: LoadingController, 
    public alert: AlertController, 
    public modal: ModalController, 
    public navCtrl: NavController) 
  {
    let _paramRoots = this.navParams.get('paramRoots');
    this._dataParam = _paramRoots;
    this.translate.get(['PAY_CHOOSE_TITLE', 'ERROR', 'ERROR_TITLE', 'OK_BUTTON']).subscribe((value) => {
			this.PayChooseRequire = value.PAY_CHOOSE_TITLE;
      this.ErrorText = value.ERROR;
      this.ErrorTitle = value.ERROR_TITLE;
      this.OKButton = value.OK_BUTTON;
    });
    this.loadPayment();
  }

  ngOnInit()
  {
    this._dataPayList = undefined;
    this._totalToPay = '-';
    this.showBlank = false;
    this.payNowButtonStatus = false;
  }

  async loadPayment()
  {
    let cluster = 'config';
    let endpoint = 'paymentConfigure';
    let loader = this.loading.create({
      spinner: 'dots',
      content: 'Loading...',
    });
    loader.present();

    let alert = this.alert.create({
      cssClass: 'no-scroll',
      title: this.ErrorText,
      message: this.PayChooseRequire,
      buttons: [{
        text: this.OKButton,
        role: 'cancel',
        handler:()=>{}
      }]
    });

    await this.reqapiprov
    .post(cluster, endpoint, {init:'configure-payment', pack:{fieldForm:{}}})
    .subscribe((res: any) => {
      let theRes = res[endpoint];
      let theItems = theRes.items;
      this.payNowButtonStatus = false;
      this.showBlank = false;
      this._dataPayList = theItems.data;
      this._dataBalance = theItems.balance;
      this._cdnIcon = theItems.server.icon;
      this._totalToPay = this._dataParam.total;
      this._currencyFormat = theItems.symbol_currency;
      loader.dismiss();
    },
    (err) => {
      this.payNowButtonStatus = false;
      this.showBlank = true;
      alert.present()
      loader.dismiss();
      this.online.checkOnline(false);
    })
  }

  choose(d?:any)
  {
    this._dataChoosePay = d;
    if(d.type != 'balance')
    {
      this.payNowButtonStatus = true;
    }
    else
    {
      this.messageBalance()
    }
  }

  messageBalance()
  {
    const selisih = this._dataBalance - this._totalToPay;
    const totalFormat = this._currencyFormat + ' ' + this.seNum.transform(this._totalToPay, 0);
    const selisihFormat = this._currencyFormat + ' ' + this.seNum.transform(selisih, 0);
    const balanceFormat = this._currencyFormat + ' ' + (this._dataBalance > 0 ? this.seNum.transform(this._dataBalance, 0) : 0);
    const yes = 'Uang saku anda akan digunakan <strong>' + totalFormat + '</strong>, sisa <u text-underline>' + selisihFormat + '</u>';
    const no = 'Uang saku anda tidak cukup untuk membayar sebesar ' + totalFormat;
    this._messageBalance = {balance:balanceFormat, message:(this._dataBalance > this._totalToPay ? yes : no), status:(this._dataBalance > this._totalToPay)};
    this.payNowButtonStatus = this._dataBalance > this._totalToPay ? true : false;
  }

  paying(p?:any)
  {
    p.data['shop_button'] = true;
    p.data['pc'] = this._dataChoosePay;
    p.data['adrd'] = this._dataParam.adrd;
    let addModal = this.modal.create(p.page, {paramRoots:p.data});
    addModal.onDidDismiss(data => {
      this.events.publish('tab:clicked',{tab:0});
      this.navCtrl.popToRoot();
    })
    addModal.present();
    this.notif.removes('cart');
  }

  async placeorder(param?:any)
  {
    let dataOrders = this._dataParam;
    dataOrders['pc'] = this._dataChoosePay;

    let cluster = 'transaction';
    let endpoint = 'addOrders';
    let loader = this.loading.create({
      spinner: 'dots',
      content: 'Loading...',
    });

    let alert = this.alert.create({
      cssClass: 'no-scroll',
      title: this.ErrorTitle,
      message: this.ErrorText,
      buttons: [{
        text: this.OKButton,
        role: 'cancel',
        handler:()=>{}
      }]
    });

    loader.present();
    await this.reqapiprov
    .post(cluster, endpoint, {init:'orders-add', pack:{fieldForm:dataOrders}})
    .subscribe((res: any)=>{
      let theRes = res[endpoint];
      let theItems = theRes.items;
      if(theItems.approve == true)
      {
        this.paying({page:param.page, data:theItems});
      }
      else
      {
        alert.present()
        //console.log(theRes)
      }
      loader.dismiss();
    },
    (err)=>{
      //console.log(err)
      loader.dismiss();
      alert.present()
      this.online.checkOnline(false);
    })
  }
}