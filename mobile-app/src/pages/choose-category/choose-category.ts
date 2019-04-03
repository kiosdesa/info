import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, ViewController, ToastController } from 'ionic-angular';
import { RequestApiProvider, Online } from './../../providers/providers'

@IonicPage()
@Component({
  selector: 'page-choose-category',
  templateUrl: 'choose-category.html',
})
export class ChooseCategoryPage 
{
  _paramRoots: any;
  _dataPassing: any;
  _cityChoose: any;

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
    this.searchCategory(this._paramRoots.query)
  }

  choose(data)
  {
    //console.log(data)
    this.view.dismiss(data)
  }

  async searchCategory(query)
  {
    const endpoint = 'searchcategoryConfigure';
    await this.api.post('config', endpoint, {init:'configure-searchcategory', pack:{fieldForm:{query:query}}})
    .subscribe((res:any)=>{
      const Resp = res[endpoint];
      if(Resp.items.approve == true)
      {
        this._dataPassing = Resp.items.data
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
