import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';
import { CartsPage } from './carts';

@NgModule({
  declarations: [
    CartsPage,
  ],
  imports: [
    IonicPageModule.forChild(CartsPage),
    TranslateModule.forChild()
  ],
})
export class CartsPageModule {}