import { Component } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { IonicPage, ViewController, NavParams, LoadingController, AlertController } from 'ionic-angular';
import { RequestApiProvider, Online } from '../../providers/providers';

@IonicPage()
@Component({
  selector: 'page-shipping-choose',
  templateUrl: 'shipping-choose.html'
})
export class ShippingChoosePage 
{
  _loopChooseShipping: any;
  _choosenShipping: any;

  ChooseAddressTitleReq: string;
  OKButton: string;
  AddressChooseTitle: string;

  constructor(
    private online: Online, 
    public view: ViewController, 
    public reqaprov: RequestApiProvider, 
    public translate: TranslateService, 
    public alert: AlertController, 
    public loading: LoadingController, 
  	public navParams: NavParams) 
  {
    this.translate.get(['USER_ADDRESS_CHOOSE_TITLE', 'USER_ADDRESS_CHOOSE', 'OK_BUTTON']).subscribe((value) => {
			this.ChooseAddressTitleReq = value.USER_ADDRESS_CHOOSE_TITLE;
      this.AddressChooseTitle = value.USER_ADDRESS_CHOOSE;
      this.OKButton = value.OK_BUTTON;
    });
    
    this.shipping(this.navParams.get('modalParam'));
  }

  choose(select?:any)
  {
    this._choosenShipping = select;
    setTimeout(()=>{
      this.done(select)
    },1000)
  }


  cancel() 
  {
    this.view.dismiss();
  }

  done(select?:any) 
  {
    this.view.dismiss(select);
  }

  async shipping(param?:any)
  {
    let alert = this.alert.create({
      cssClass: 'no-scroll',
      title: this.AddressChooseTitle,
      message: this.ChooseAddressTitleReq,
      buttons: [
      {
        text: this.OKButton,
        role: 'cancel',
        handler: () => {
          this.cancel()
        }
      }]
    });

    if(param.to == undefined)
    {
      alert.present()
    }
    else
    {
      let cluster = 'transaction';
      let endpoint = 'listsShipping';
      let loader = this.loading.create({
        spinner: 'dots',
        content: 'Loading...',
      });
      loader.present();
      await this.reqaprov
      .post(cluster, endpoint, {init:'shipping-lists', pack:{fieldForm:param}})
      .subscribe((res: any) => {
        let theRes = res[endpoint];
        let theItems = theRes.items;
        if(theItems.approve == true)
        {
          this._loopChooseShipping = theItems.data;
        }
        else
        {
          this._loopChooseShipping = undefined;
          alert.present();
        }
        loader.dismiss();
      },
      (err) => {
        alert.present();
        loader.dismiss();
        this.online.checkOnline(false);
      })
    }
  }
}