import { Injectable } from '@angular/core';
import { Storage } from "@ionic/storage";

@Injectable()
export class NotifyProvider 
{
	_suffixName: string = '_notif';

	constructor(
		private storage: Storage) 
	{}

	get(indexNotif?:any)
	{
		// indexNotif = cart_notif (sample)
		let name = indexNotif + this._suffixName;
		return this.storage.get(name).then(count => {
			return count;
		})
	}

	store(data?: any, indexNotif?:string)
	{
		let name = indexNotif + this._suffixName;
		let tmpc = this.get(indexNotif).then((val)=>{ return val ? val : 0; })
		if(tmpc == data) 
		{
			return tmpc
		}
		else
		{
			return this.storage.set(name, data).then(val=>{
				return {orinot:tmpc, lastnot:val};
			})
		}
	}

	removes(indexNotif?:any)
	{
		let name = indexNotif + this._suffixName;
		if(this.storage.get(name)) this.storage.remove(name);
	}

	removesAll()
	{
		this.storage.forEach((index, key, value)=>{
			if(/_notif/i.test(key))
			{
				this.storage.remove(key)
			}
		})
	}
}
