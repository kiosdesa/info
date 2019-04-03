import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, ToastController, LoadingController } from 'ionic-angular';
import { RequestApiProvider, Online } from './../../providers/providers'
@IonicPage()
@Component({
  selector: 'page-kiosku-product',
  templateUrl: 'kiosku-product.html',
})
export class KioskuProductPage 
{
  _paramRoots: any;
  _segmentListProduct: string = 'publish';
  _listProductPush: any = [];
  _cdnServer: any;
  _paramSearch: string = undefined;
  _showBlank: boolean = false;

  constructor(
    private api: RequestApiProvider, 
    private online: Online, 
    public toast: ToastController, 
    public navCtrl: NavController, 
    public loading: LoadingController, 
    public navParams: NavParams)
  {
    this._paramRoots = this.navParams.get('paramRoots')
    if(this._paramRoots == undefined)
    {
      this._showBlank = true
    }
    else
    {
      if('seller_id' in this._paramRoots && 'seller_slug' in this._paramRoots)
      {
        this._showBlank = false
      }
      else
      {
        this._showBlank = true
      }
    }
  }

  ionViewDidEnter()
  {
    this.getData(this._segmentListProduct)
  }

  async getData(segment?:string)
  {
    let loader = this.loading.create({spinner: 'dots', content: 'Loading...'});
    loader.present();
    segment = segment ? segment : this._segmentListProduct;
    this._segmentListProduct = segment;
    const status = segment == 'draft' ? 0 : 1;
    let parameter = {filter:this._paramRoots.seller_id, status:status, simple:true}
    if(this._paramSearch != undefined) parameter['search'] = this._paramSearch;
    const endpoint = 'lookupProduct';
    await this.api.post('sensus', endpoint, {init:'product-lookup', pack:{fieldForm:parameter, filterType:'seller'}})
    .subscribe((res:any)=>{
      const Resp = res[endpoint];
      if(Resp.items.approve == true)
      {
        this._listProductPush = Resp.items.product
        this._cdnServer = Resp.items.server;
      }
      else
      {
        this.showToast(Resp.items.message, 'warning-toast', 1300)
      }
      loader.dismiss()
    },
    (err:any)=>{
      loader.dismiss()
      this.showToast(err.message, 'danger-toast')
      this.online.checkOnline(true)
    })
  }

  searchTyping(e)
  {
    let val = e.target.value;
    this._paramSearch = val;
  }

  searchPress(key)
  {
    if(key.charCode == 13)
    {
      if(this._paramSearch == undefined || this._paramSearch == '')
      {
				this.getData(this._segmentListProduct); 
      }
      else
      {
        if(this._paramSearch.length > 3)
        {
					this.getData(this._segmentListProduct)
        }
        else
        {
					this.getData(this._segmentListProduct); 
        }
      }
    }
  }

  pushPage(page, param)
  {
    param = param ? {paramRoots:param} : param;
    this.navCtrl.push(page, param, {animate:true})
  }

  showToast(message, color?:string, duration?:number)
  {
    color = color ? color : '';
    duration = duration ? duration : 3000;

    let toast = this.toast.create({
      position: 'top',
      cssClass: color,
      message: message,
      duration: duration
    })
    toast.present();
  }
}
