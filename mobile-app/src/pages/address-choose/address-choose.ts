import { Component } from '@angular/core';
import { IonicPage, ViewController, NavParams, LoadingController, ModalController } from 'ionic-angular';
import { RequestApiProvider, Online } from '../../providers/providers';
//import { Observable } from 'rxjs/Observable';

@IonicPage()
@Component({
  selector: 'page-address-choose',
  templateUrl: 'address-choose.html'
})
export class AddressChoosePage 
{
  _loopChooseAddress: any;
  _choosenAddress: any;

  constructor(
    private online: Online, 
    public view: ViewController, 
    public reqaprov: RequestApiProvider, 
    public modalCtrl: ModalController, 
    public loading: LoadingController, 
  	public navParams: NavParams) 
  {
    this.address();
  }

  choose(select?:any)
  {
    this._choosenAddress = select;
    setTimeout(()=>{
      this.done(select)
    },600)
  }

  cancel() 
  {
    this.view.dismiss();
  }

  done(select?:any) 
  {
    this.view.dismiss(select);
  }

  async address()
  {
    let cluster = 'account';
    let endpoint = 'addressBuyer';
    let loader = this.loading.create({
      spinner: 'dots',
      content: 'Loading...',
    });
    loader.present();
    await this.reqaprov
    .post(cluster, endpoint, {init:'buyer-address', pack:{}})
    .subscribe((res: any) => {
      let theRes = res[endpoint];
      let theItems = theRes.items;
      if(theItems.approve == true)
      {
        this._loopChooseAddress = theItems.data;
      }
      else
      {
        this._loopChooseAddress = undefined;
        this.cancel()
      }
      loader.dismiss();
    },
    (err) => {
      this.cancel()
      loader.dismiss();
      this.online.checkOnline(false);
    })
  }
}