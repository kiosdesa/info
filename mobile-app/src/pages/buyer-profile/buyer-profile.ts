import { Component } from '@angular/core';
import { TranslateService } from '@ngx-translate/core'; 
import { IonicPage, NavController, NavParams, LoadingController, ToastController, AlertController } from 'ionic-angular';
import { RequestApiProvider, User, Online} from './../../providers/providers';

@IonicPage()
@Component({
  selector: 'page-buyer-profile',
  templateUrl: 'buyer-profile.html',
})
export class BuyerProfilePage 
{
  _userPushFirstLoad: any = undefined;
  _dataBuyer: any = {};
  _dataSelf: any = {};
  _dataKios: any;
  _dataFollowed: any = [];
  _symbolKurs: string;
  
  _serverCDN: any = {};
  _showKiosNew: boolean = false;
  _showBlank: boolean = false;

  tabSegment: string = 'buyer';
  menuBuyer: any = [{}];

  private okButton: string;
  private atemptMessage: string;
  private emptyMessage: string;
  private emptyShippingMessage: string;

  constructor(
    private user: User, 
    private api: RequestApiProvider, 
    private online: Online, 
    public translate: TranslateService, 
    public navCtrl: NavController, 
    public loading: LoadingController, 
    public alert: AlertController, 
    public toast: ToastController, 
    public navParams: NavParams) 
  {
    this._userPushFirstLoad = this.user._globalUserData;
    this.translate.get([
      'CART_ORDER_TITLE', 'PAY_CONFIRM_BUTTON', 'USER_ADDRESS_TITLE', 'PAY_CODE_ACCOUNT_NAME', 'ATTENTION', 
      'OK_BUTTON', 'ORDER_EMPTY', 'SHOP_USER_SHIPPING_TITLE'
    ]).subscribe((val)=>{
      this.menuBuyer = [
        {title:val.USER_ADDRESS_TITLE, page:'AddressChoosePage', icon:'md-at', data:undefined},
        {title:val.PAY_CODE_ACCOUNT_NAME, page:'BuyerPaymentPage', icon:'ios-card', data:undefined},
        {title:val.CART_ORDER_TITLE, page:'OrderPage', icon:'ios-basket-outline', data:{type:'badge', value:0, compareValue:0, param:{}}},
        {title:val.PAY_CONFIRM_BUTTON, page:'OrderPage', icon:'ios-cash-outline', data:{type:'badge', value:0, compareValue:0, param:{segment:'verify', receive:0, shipping:[1], group:true}}}
      ]
      
      this.emptyShippingMessage = val.SHOP_USER_SHIPPING_TITLE;
      this.okButton = val.OK_BUTTON;
      this.atemptMessage = val.ATTENTION;
      this.emptyMessage = val.ORDER_EMPTY;
    })
  }

  ionViewDidEnter()
  {
    if(this.tabSegment == 'buyer') this.loadBuyer()
    if(this.tabSegment == 'kios') this.loadKios()
  }

  async loadBuyer()
  {
    let cluster = 'account';
    let endpoint = 'cardBuyer';
    let loader = this.loading.create({
      spinner: 'dots',
      content: 'Loading...',
    });
    loader.present();
    await this.api
    .post(cluster, endpoint, {init:'buyer-card', pack:{}})
    .subscribe((res: any) => {
      let theRes = res[endpoint];
      let theItems = theRes.items;
      if(theItems.approve == true)
      {
        this._dataBuyer = theItems.data;
        this._dataSelf = theItems.data.self;
        this._dataFollowed = theItems.data.followed;
        this._symbolKurs = theItems.symbol;
        this.menuBuyer[2].data.value = theItems.data.order.process;
        this.menuBuyer[2].data.compareValue = theItems.data.order.placed;
        this.menuBuyer[3].data.value = theItems.data.order.verify;
        this._serverCDN = theItems.server;
        this._showBlank = false;
      }
      else
      {
        this._showBlank = true;
      }
      loader.dismiss();
    },
    (err) => {
      this._showBlank = true;
      loader.dismiss();
      this.online.checkOnline(false);
    })
  }

  async loadKios()
  {
    let cluster = 'account';
    let endpoint = 'kiosSeller';
    let loader = this.loading.create({
      spinner: 'dots',
      content: 'Loading...',
    });
    loader.present();
    await this.api
    .post(cluster, endpoint, {init:'seller-kios', pack:{fieldForm:{owner:this._dataSelf.idsecure}}})
    .subscribe((res: any) => {
      let theRes = res[endpoint];
      let theItems = theRes.items;
      this._showBlank = false;
      if(theItems.approve == true)
      {
        this._dataKios = theItems.data;
        this._showKiosNew = false;
      }
      else
      {
        this._showKiosNew = true;
      }
      loader.dismiss();
    },
    (err) => {
      this._showBlank = true;
      loader.dismiss();
      this.online.checkOnline(false);
    })
  }

  pushPage(page, data?:any)
  {
    if(data) if("value" in data) if(data.value < 1) return this.openAlert(this.emptyMessage, this.atemptMessage)
    const rootparam = data ? ("param" in data ? {paramRoots:data.param} : {paramRoots:data}) : {};
    this.navCtrl.push(page, rootparam,
      {
        animate:true,
        direction: 'enter'
      }
    );
  }

  async followSeller(param?:any)
  {
    const paramsObject = {
      followed: (param.followed ? param.followed : this._dataFollowed[param.index].followed),
      id_seller: param.id,
      slug_seller: param.slug
    };
    
    let cluster = 'cabinet';
    let endpoint = 'followingSeller';
    let loader = this.loading.create({
      spinner: 'dots',
      content: 'Loading...',
    });
    loader.present();
    await this.api
    .post(cluster, endpoint, {init:'seller-following', pack:{fieldForm:paramsObject}})
    .subscribe((res:any)=>{
      let theRes = res[endpoint];
      let theItems = theRes.items;
      if(theItems.approve == true)
      {
        this._dataFollowed[param.index].followed = theItems.followed;
        this.showToast(theItems.message, 'success-toast')
      }
      loader.dismiss();
    },
		(err)=>{
      loader.dismiss();
      this.online.checkOnline(false);
    })
  }

  async updateKiosk(fields)
  {
    if(this.tabSegment == 'kios')
    {
      let loader = this.loading.create({spinner: 'dots', content: 'Loading...'});
      loader.present();
      const endpoint = 'modifySeller'
      this.api.post('account', endpoint, {init:'seller-modify', pack:{fieldForm:fields}})
      .subscribe((res:any)=>{
        const Resp = res[endpoint];
        if(Resp.items.approve == true)
        {
          this.showToast(Resp.items.message, 'info-toast')
        }
        else
        {
          this.showToast(Resp.items.message, 'danger-toast')
        }
        loader.dismiss()
      },
      (err)=>{
        loader.dismiss()
        this.online.checkOnline(true)
      })
    }
  }

  chooseKioskShipping()
  {
    if(this.tabSegment == 'kios')
    {
      let loader = this.loading.create({spinner: 'dots', content: 'Loading...'});
      loader.present();
      const endpoint = 'shippingConfigure'
      this.api.post('config', endpoint, {init:'configure-shipping', pack:{fieldForm:{formatted:true}}})
      .subscribe((res:any)=>{
        let alert = this.alert.create({
          subTitle:this.emptyShippingMessage,
          inputs: res[endpoint].items,
          buttons: [{
            text: 'Ok',
            handler: data => {
              if(data.length > 0)
              {
                this.updateKiosk({shipping:data}).then(()=>{
                  this._dataKios[0].shipping = data;
                })
                return true;
              }
              else
              {
                this.showToast(this.emptyShippingMessage, 'danger-toast')
                return true;
              }
            }
          }]
        });
        alert.present()
        loader.dismiss()
      },
      (err)=>{
        loader.dismiss()
        this.online.checkOnline(true)
      })
    }
  }

  openAlert(message, title)
  {
    let alert = this.alert.create({
      cssClass: 'no-scroll',
      title: title,
      message: message,
      buttons: [{
        text: this.okButton,
        role: 'cancel',
        handler: () => {
          //this.navCtrl.pop()
        }
      }]
    });
    alert.present();
  }

  showToast(message?:string, color?:string, duration?:number)
  {
    const colors = color ? color : '';
    const durations = duration ? duration : 3000;
    let toast = this.toast.create({
      message: message,
      cssClass: colors,
      duration: durations,
      position: 'top'
    });
    toast.present();
  }
}
