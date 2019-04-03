import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, ViewController, ToastController } from 'ionic-angular';
import { RequestApiProvider, Online } from './../../providers/providers'

@IonicPage()
@Component({
  selector: 'page-city-search',
  templateUrl: 'city-search.html',
})
export class CitySearchPage 
{
  _paramRoots: any;
  _dataPassing: any;
  _next: boolean = false;
  _defaultPages: number = 1;
  _cityChoose: any;
  _defaultTitle:string;

  constructor(
    private api: RequestApiProvider, 
    private online: Online, 
    public view: ViewController, 
    public navCtrl: NavController, 
    public toast: ToastController, 
    public navParams: NavParams)
  {
    this._paramRoots = this.navParams.get('paramRoots')
  }

  ionViewDidEnter()
  {
    //console.log(this._paramRoots)
    if(this._paramRoots.field == 'postal_code')
    {
      this._dataPassing = this._paramRoots.temp
    }
    else
    {
      this.getCityShipping(this._paramRoots.query)
    }
  }

  choose(data)
  {
    //console.log(data)
    this.view.dismiss(data)
  }

  nextCity()
  {
    this._defaultPages = this._next == true ? this._defaultPages + 1 : this._defaultPages;
    this.getCityShipping(this._paramRoots.query, this._defaultPages)
  }

  async getCityShipping(query, page?:number)
  {
    const param = {type:'tokped', to:query};
    if(page) param['page'] = page;
    const endpoint = 'cityShipping';
    await this.api.post('config', endpoint, {init:'shipping-city', pack:{fieldForm:param}})
    .subscribe((res:any)=>{
      const Resp = res[endpoint];
      if(Resp.items.approve == true)
      {
        this._dataPassing = Resp.items.data
        this._next = Resp.items.next;
        this._defaultTitle = Resp.items.title;
      }
      else
      {
        this._dataPassing = undefined
        this.showToast(Resp.items.message, 'danger-toast')
        this.view.dismiss()
      }
    },
    (err)=>{
      this.view.dismiss()
      this.showToast('Error', 'danger-toast')
      this.online.checkOnline(true)
    })
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
